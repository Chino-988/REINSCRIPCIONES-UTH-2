<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\{Pago, Reinscripcion, Estudiante, Notificacion};

class AdminController extends Controller
{
    // ===== DASHBOARD =====
    public function dashboard()
    {
        $pendCaja   = Pago::where('estatus_caja', 'PENDIENTE')->count();
        $pendAdmin  = Pago::where('estatus_caja', 'VALIDADO')
                          ->where(function($q){
                              $q->whereNull('estatus_admin')
                                ->orWhere('estatus_admin', 'PENDIENTE');
                          })->count();
        $aprobadas  = Reinscripcion::where('estatus_final', 'APROBADA')->count();
        $rechazadas = Reinscripcion::where('estatus_final', 'RECHAZADA')->count();

        $ultimos = Pago::with('estudiante')->latest()->take(10)->get();

        return view('admin.dashboard', compact(
            'pendCaja','pendAdmin','aprobadas','rechazadas','ultimos'
        ));
    }

    // ===== LISTA DE SOLICITUDES =====
    public function solicitudes(Request $request)
    {
        $q = trim((string)$request->input('q', ''));

        $pagos = Pago::with('estudiante')
            ->when($q !== '', function($qb) use ($q) {
                $qb->where('referencia', 'ilike', "%{$q}%")
                   ->orWhereHas('estudiante', function($s) use ($q){
                       $s->where('matricula','ilike',"%{$q}%")
                         ->orWhere('nombre','ilike',"%{$q}%")
                         ->orWhere('apellido_paterno','ilike',"%{$q}%")
                         ->orWhere('apellido_materno','ilike',"%{$q}%");
                   });
            })
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        $pendCaja  = Pago::where('estatus_caja', 'PENDIENTE')->count();
        $pendAdmin = Pago::where('estatus_caja', 'VALIDADO')
                         ->where(function($x){
                             $x->whereNull('estatus_admin')
                               ->orWhere('estatus_admin','PENDIENTE');
                         })->count();

        return view('admin.solicitudes', compact('pagos','q','pendCaja','pendAdmin'));
    }

    public function exportSolicitudes(Request $request)
    {
        $file = 'solicitudes_'.date('Ymd_His').'.csv';
        $rows = Pago::with('estudiante')->latest()->get();

        $out = fopen('php://temp','w+');
        fputcsv($out, ['id','matricula','nombre','referencia','estatus_caja','estatus_admin','creado']);
        foreach ($rows as $p) {
            fputcsv($out, [
                $p->id,
                optional($p->estudiante)->matricula,
                optional($p->estudiante)->nombre.' '.optional($p->estudiante)->apellido_paterno.' '.optional($p->estudiante)->apellido_materno,
                $p->referencia,
                $p->estatus_caja,
                $p->estatus_admin,
                $p->created_at,
            ]);
        }
        rewind($out);
        $csv = stream_get_contents($out);
        fclose($out);

        return response($csv)
            ->header('Content-Type','text/csv')
            ->header('Content-Disposition','attachment; filename="'.$file.'"');
    }

    // ===== REVISAR UNA SOLICITUD =====
    public function verSolicitud(Pago $pago)
    {
        $pago->load([
            'estudiante',
            'estudiante.condicion',
            'estudiante.reinscripcion',
        ]);

        return view('admin.ver', compact('pago'));
    }

    // ===== APROBAR Y EMITIR CONSTANCIA =====
    public function finalizar(Request $request, Pago $pago)
    {
        if ($pago->estatus_caja !== 'VALIDADO') {
            return back()->with('err', 'Aún falta validación de Caja.');
        }

        $pago->estatus_admin       = 'VALIDADO';
        $pago->observaciones_admin = $request->input('observaciones');
        $pago->save();

        $rein = Reinscripcion::firstOrNew(['estudiante_id' => $pago->estudiante_id]);
        if (!$rein->exists) {
            $rein->token_verificacion = (string) Str::uuid();
        }
        $rein->estatus_final = 'APROBADA';
        $rein->save();

        $pdfRelPath = $this->generarConstanciaPDF($pago->estudiante, $rein);
        $rein->constancia_pdf_path = $pdfRelPath;
        $rein->save();

        Notificacion::toUser(
            $pago->estudiante->user_id,
            'Reinscripción aprobada',
            'Tu constancia está lista para descargar en tu panel.'
        );

        return redirect()->route('admin.ver', $pago)
                         ->with('ok', 'Aprobado y constancia emitida. Se notificó al estudiante.');
    }

    // ===== RECHAZAR =====
    public function rechazar(Request $request, Pago $pago)
    {
        $request->validate(['motivo'=>'required|string|max:300']);

        $pago->estatus_admin       = 'RECHAZADO';
        $pago->observaciones_admin = $request->motivo;
        $pago->save();

        $estu = $pago->estudiante;
        if ($estu) {
            $estu->validado_en     = null;
            $estu->validado_datos  = false;
            $estu->save();
        }

        Notificacion::toUser(
            $pago->estudiante->user_id,
            'Reinscripción rechazada',
            'Motivo: '.$request->motivo
        );

        return back()->with('ok','Solicitud rechazada y notificada al estudiante.');
    }

    public function regenerarConstancia(Request $request, Pago $pago)
    {
        $rein = Reinscripcion::where('estudiante_id', $pago->estudiante_id)->first();
        if (!$rein || $rein->estatus_final !== 'APROBADA') {
            return back()->with('err', 'No hay reinscripción aprobada para regenerar.');
        }

        $pdfRelPath = $this->generarConstanciaPDF($pago->estudiante, $rein, true);
        $rein->constancia_pdf_path = $pdfRelPath;
        $rein->save();

        return back()->with('ok', 'Constancia regenerada.');
    }

    // ===== Helper: generar PDF (QR en SVG, sin imagick) =====
    private function generarConstanciaPDF($estu, $rein, $forceNew = false)
    {
        $urlVerif = route('verificacion.mostrar', $rein->token_verificacion);

        // Intentar QR en SVG (no requiere imagick)
        $qrSvg = null;
        if (class_exists('\SimpleSoftwareIO\QrCode\Facades\QrCode')) {
            try {
                $qrSvg = \SimpleSoftwareIO\QrCode\Facades\QrCode::format('svg')
                    ->size(220)->margin(1)->generate($urlVerif);
            } catch (\Throwable $e) {
                $qrSvg = null; // si algo pasa, seguimos sin QR
            }
        }

        $html = view('pdf.constancia', [
            'estu'     => $estu,
            'rein'     => $rein,
            'urlVerif' => $urlVerif,
            'qrSvg'    => $qrSvg,   // <-- pasamos SVG
        ])->render();

        $pdf = Pdf::loadHTML($html)->setPaper('letter', 'portrait');

        Storage::disk('public')->makeDirectory('constancias');
        $file = $forceNew
            ? ('constancias/constancia_'.$estu->matricula.'_'.$rein->id.'_'.time().'.pdf')
            : ('constancias/constancia_'.$estu->matricula.'_'.$rein->id.'.pdf');

        Storage::disk('public')->put($file, $pdf->output());
        return $file;
    }
}
