<?php

namespace App\Http\Controllers\Caja;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\{Pago, Estudiante, Notificacion};

class CajaController extends Controller
{
    public function dashboard()
    {
        $totalPend  = Pago::where(function($q){
            $q->whereNull('estatus_caja')
              ->orWhereIn('estatus_caja', ['PENDIENTE','EN_PROCESO']);
        })->count();

        $totalOk    = Pago::where('estatus_caja','VALIDADO')->count();
        $totalRech  = Pago::where('estatus_caja','RECHAZADO')->count();

        return view('caja.dashboard', compact('totalPend','totalOk','totalRech'));
    }

    /**
     * NUEVO: métricas del dashboard (se usa vía fetch/AJAX).
     * No toca vistas ni estilos.
     */
    public function metrics(Request $request)
    {
        $pend  = Pago::where(function($q){
            $q->whereNull('estatus_caja')
              ->orWhereIn('estatus_caja', ['PENDIENTE','EN_PROCESO']);
        })->count();

        $ok    = Pago::where('estatus_caja','VALIDADO')->count();
        $rech  = Pago::where('estatus_caja','RECHAZADO')->count();

        return response()->json([
            'ok'   => true,
            'data' => [
                'pendientes' => $pend,
                'validados'  => $ok,
                'rechazados' => $rech,
            ],
        ]);
    }

    public function pendientes(Request $request)
    {
        $q = trim((string)$request->input('q',''));
        $estado = strtoupper($request->input('estado','PENDIENTES'));
        $desde = $request->input('desde');
        $hasta = $request->input('hasta');

        $query = Pago::with('estudiante');

        switch ($estado) {
            case 'PENDIENTES':
                $query->where(function($qq){
                    $qq->whereNull('estatus_caja')->orWhere('estatus_caja','PENDIENTE');
                });
                break;
            case 'EN_PROCESO':
                $query->where('estatus_caja','EN_PROCESO'); break;
            case 'VALIDADOS':
                $query->where('estatus_caja','VALIDADO'); break;
            case 'RECHAZADOS':
                $query->where('estatus_caja','RECHAZADO'); break;
            case 'TODOS':
            default:
                break;
        }

        if ($q !== '') {
            $query->where(function($qq) use ($q) {
                $qq->where('referencia','ILIKE',"%{$q}%")
                   ->orWhereHas('estudiante', function($qe) use ($q) {
                       $qe->where('matricula','ILIKE',"%{$q}%")
                          ->orWhere('nombre','ILIKE',"%{$q}%")
                          ->orWhere('apellido_paterno','ILIKE',"%{$q}%");
                   });
            });
        }

        if ($desde) $query->whereDate('created_at','>=',$desde);
        if ($hasta) $query->whereDate('created_at','<=',$hasta);

        $pagos = $query->orderBy('created_at','desc')->paginate(15)->withQueryString();

        return view('caja.pendientes', compact('pagos','q','estado','desde','hasta'));
    }

    public function validar(Request $request, Pago $pago)
    {
        $pago->estatus_caja = 'VALIDADO';
        if ($request->filled('observaciones')) $pago->observaciones_caja = $request->input('observaciones');
        $pago->save();

        Notificacion::toRole('ADMIN','Pago validado por Caja', "Pago #{$pago->id} ({$pago->referencia}) validado.");
        Notificacion::toUser($pago->estudiante->user_id, 'Caja validó tu pago', "Pago #{$pago->id} validado por Caja.");

        return back()->with('ok','Pago validado por Caja.');
    }

    public function rechazar(Request $request, Pago $pago)
    {
        $request->validate(['motivo'=>'required|string|max:300']);
        $pago->estatus_caja = 'RECHAZADO';
        $pago->observaciones_caja = $request->motivo;
        $pago->save();

        Notificacion::toRole('ADMIN','Pago rechazado por Caja', "Pago #{$pago->id} rechazado. Motivo: {$request->motivo}");
        Notificacion::toUser($pago->estudiante->user_id, 'Caja rechazó tu pago', "Motivo: {$request->motivo}. Puedes reenviar el pago.");

        return back()->with('ok','Pago rechazado y motivo registrado.');
    }

    // Exportaciones
    public function exportarCSV(Request $request)
    {
        $rows = Pago::with('estudiante')->orderBy('id','desc')->limit(1000)->get();
        $filename = 'export_pagos_'.now()->format('Ymd_His').'.csv';
        $handle = fopen(storage_path('app/'.$filename), 'w+');
        fputcsv($handle, ['id','matricula','alumno','referencia','estatus_caja','estatus_admin','fecha']);
        foreach ($rows as $p) {
            fputcsv($handle, [
                $p->id,
                $p->estudiante->matricula ?? '',
                trim(($p->estudiante->nombre ?? '').' '.($p->estudiante->apellido_paterno ?? '')),
                $p->referencia,
                $p->estatus_caja,
                $p->estatus_admin,
                optional($p->created_at)->format('Y-m-d H:i:s'),
            ]);
        }
        fclose($handle);
        return response()->download(storage_path('app/'.$filename))->deleteFileAfterSend(true);
    }

    public function exportarPendientes()
    {
        $rows = Pago::with('estudiante')
            ->where(function($q){
                $q->whereNull('estatus_caja')->orWhere('estatus_caja','PENDIENTE');
            })
            ->orderBy('id','desc')->get();

        $filename = 'pendientes_caja_'.now()->format('Ymd_His').'.csv';
        $handle = fopen(storage_path('app/'.$filename), 'w+');
        fputcsv($handle, ['id','matricula','alumno','referencia','fecha']);
        foreach ($rows as $p) {
            fputcsv($handle, [
                $p->id,
                $p->estudiante->matricula ?? '',
                trim(($p->estudiante->nombre ?? '').' '.($p->estudiante->apellido_paterno ?? '')),
                $p->referencia,
                optional($p->created_at)->format('Y-m-d H:i:s'),
            ]);
        }
        fclose($handle);
        return response()->download(storage_path('app/'.$filename))->deleteFileAfterSend(true);
    }

    // ====== ANÁLISIS (visible) =====
    public function analisis()
    {
        return view('caja.analisis', [
            'preview' => session('analisis_preview', null),
            'total'   => session('analisis_total', null),
            'ok'      => session('ok', null),
            'err'     => session('err', null),
        ]);
    }

    public function procesarAnalisis(\Illuminate\Http\Request $request)
    {
        $request->validate([
            'archivo' => 'required|file|mimes:csv,txt|max:10240',
        ], [
            'archivo.mimes' => 'Por ahora acepta CSV. Si es Excel, expórtalo como .csv.',
        ]);

        Storage::disk('local')->makeDirectory('analisis');
        Storage::disk('local')->makeDirectory('analisis/out');

        $path = $request->file('archivo')->store('analisis', 'local');
        $abs  = Storage::disk('local')->path($path);

        if (!file_exists($abs)) {
            return back()->with('err', 'No se pudo guardar el archivo para procesarlo.')->withInput();
        }

        $handle = fopen($abs, 'r');
        if (!$handle) return back()->with('err', 'No se pudo abrir el archivo.')->withInput();

        $firstLine = fgets($handle);
        $delimCandidate = (substr_count($firstLine, ';') > substr_count($firstLine, ',')) ? ';' : ',';
        rewind($handle);

        $headers = [];
        $rows    = [];
        $line    = 0;
        while (($data = fgetcsv($handle, 0, $delimCandidate)) !== false) {
            if ($line === 0 && isset($data[0])) {
                $data[0] = preg_replace('/^\xEF\xBB\xBF/', '', $data[0]);
            }
            if ($line === 0) {
                $headers = array_map(fn($h) => trim(Str::lower($h)), $data);
            } else {
                $rows[]  = $data;
            }
            $line++;
        }
        fclose($handle);

        $hIndex = function($name) use ($headers) {
            foreach ($headers as $i => $h) {
                if ($h === $name) return $i;
            }
            return null;
        };

        $idxRef  = $hIndex('referencia') ?? $hIndex('ref');
        $idxMat  = $hIndex('matricula')  ?? $hIndex('mtricula');
        $idxMonto= $hIndex('monto');
        $idxFecha= $hIndex('fecha');

        $outHeaders = array_merge($headers, ['estatus_analisis', 'detalle']);
        $outRows    = [];

        foreach ($rows as $r) {
            $ref = $idxRef !== null && isset($r[$idxRef]) ? preg_replace('/\D+/', '', $r[$idxRef]) : null;
            $ok  = $ref && strlen($ref) >= 20 && strlen($ref) <= 30;

            $estatus = $ok ? 'OK' : 'ERROR';
            $detalle = $ok ? 'Referencia con longitud válida' : 'Referencia ausente o longitud inválida (esperado 20-30 dígitos)';

            $outRows[] = array_merge($r, [$estatus, $detalle]);
        }

        $outName = 'analisis/out/resultado_' . date('Ymd_His') . '.csv';
        $outAbs  = Storage::disk('local')->path($outName);

        $fh = fopen($outAbs, 'w');
        if (!$fh) {
            return back()->with('err', 'No se pudo escribir el archivo de salida.')->withInput();
        }
        fputcsv($fh, $outHeaders);
        foreach ($outRows as $row) {
            fputcsv($fh, $row);
        }
        fclose($fh);

        $preview = [];
        $limit = min(10, count($outRows));
        for ($i=0; $i<$limit; $i++) {
            $preview[] = $outRows[$i];
        }

        session([
            'analisis_preview' => [
                'headers' => $outHeaders,
                'rows'    => $preview,
            ],
            'analisis_total'   => count($outRows),
            'analisis_out'     => $outName,
            'ok'               => 'Archivo procesado correctamente.',
        ]);

        return redirect()->route('caja.analisis');
    }

    public function descargarAnalisis(\Illuminate\Http\Request $request)
    {
        $file = session('analisis_out');
        if (!$file || !Storage::disk('local')->exists($file)) {
            abort(404, 'No hay archivo listo para descargar.');
        }
        return response()->download(Storage::disk('local')->path($file), basename($file), [
            'Content-Type' => 'text/csv; charset=utf-8',
        ]);
    }

    /**
     * NUEVO: Consulta de estado por referencia (AJAX interno).
     * Recibe: referencia (string/número).
     * Devuelve: estatus_caja y observaciones en JSON.
     */
    public function estadoAjax(Request $request)
    {
        $ref = preg_replace('/\D+/', '', (string) $request->input('referencia'));

        if ($ref === '') {
            return response()->json([
                'ok' => false,
                'error' => 'Referencia requerida.',
            ], 422);
        }

        $pago = Pago::with('estudiante')
            ->where('referencia', $ref)
            ->orderBy('id', 'desc')
            ->first();

        if (!$pago) {
            return response()->json([
                'ok' => false,
                'error' => 'Pago no encontrado',
            ], 404);
        }

        return response()->json([
            'ok'   => true,
            'data' => [
                'pago_id'       => $pago->id,
                'referencia'    => $pago->referencia,
                'estatus_caja'  => $pago->estatus_caja ?? 'PENDIENTE',
                'observaciones' => $pago->observaciones_caja,
                'alumno'        => [
                    'matricula' => $pago->estudiante->matricula ?? null,
                    'nombre'    => trim(($pago->estudiante->nombre ?? '').' '.($pago->estudiante->apellido_paterno ?? '')),
                ],
            ],
        ]);
    }
}
