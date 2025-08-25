<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\{Estudiante, Notificacion};

class DashboardController extends Controller
{
    public function estudiante()
    {
        $user = auth()->user();
        $estu = Estudiante::where('user_id', $user->id)->first();

        // Usa la relación latestOfMany que pusimos en el modelo
        $rein = $estu ? $estu->reinscripcion : null;

        // Pago más reciente (si necesitas)
        $pago = $estu ? $estu->pagos()->latest()->first() : null;

        $notis = Notificacion::where('user_id', $user->id)->latest()->take(8)->get();

        // ¿perfil enviado?
        $perfilEnviado = false;
        if ($estu) {
            $perfilEnviado = (bool)($estu->validado_en)
                            || (bool) data_get($estu,'validado_datos',false)
                            || (bool) session('perfil_enviado', false);
        }

        $bloqPerfil  = $perfilEnviado;
        $permitePago = $perfilEnviado && ( !$pago || (($pago->estatus_caja ?? null) === 'RECHAZADO') );
        $bloqPagoBtn = ! $permitePago;

        return view('estudiante.dashboard', compact(
            'estu','pago','rein','notis',
            'perfilEnviado','bloqPerfil','permitePago','bloqPagoBtn'
        ));
    }

    public function descargarConstancia()
    {
        $estu = Estudiante::where('user_id', auth()->id())->firstOrFail();
        $rein = $estu->reinscripcion; // más reciente
        abort_unless($rein && $rein->estatus_final === 'APROBADA' && $rein->constancia_pdf_path, 403);
        return Storage::disk('public')->download($rein->constancia_pdf_path, 'Constancia_Reinscripcion.pdf');
    }

    public function notificaciones()
    {
        $notis = Notificacion::where('user_id', auth()->id())->orderBy('created_at','desc')->paginate(20);
        return view('estudiante.notificaciones', compact('notis'));
    }

    public function marcarNotiLeida($id)
    {
        $n = Notificacion::where('user_id', auth()->id())->where('id',$id)->firstOrFail();
        $n->leida = true; $n->save();
        return back()->with('ok','Notificación marcada como leída.');
    }
}
