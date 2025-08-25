<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\{Estudiante, Pago, Notificacion};

class PagoController extends Controller
{
    public function create()
    {
        $estu = Estudiante::where('user_id', auth()->id())->firstOrFail();

        // Requiere haber enviado la validación de datos antes de poder cargar pago
        $perfilEnviado = (bool)($estu->validado_en) || (bool) data_get($estu,'validado_datos',false) || (bool) session('perfil_enviado', false);
        if (!$perfilEnviado) {
            return redirect()->route('dash.estudiante')->with('info','Primero envía la Validación de datos. Después podrás cargar tu pago.');
        }

        $last = $estu->pagos()->latest()->first();
        if ($last && ($last->estatus_caja ?? null) !== 'RECHAZADO') {
            return redirect()->route('dash.estudiante')->with('info','Ya tienes un pago enviado o validado. Espera resolución o rechazo para reenviar.');
        }

        return view('estudiante.pago');
    }

    public function store(Request $request)
    {
        $estu = Estudiante::where('user_id', auth()->id())->firstOrFail();

        $perfilEnviado = (bool)($estu->validado_en) || (bool) data_get($estu,'validado_datos',false) || (bool) session('perfil_enviado', false);
        if (!$perfilEnviado) {
            return redirect()->route('dash.estudiante')->with('err','Primero envía la Validación de datos.');
        }

        $last = $estu->pagos()->latest()->first();
        if ($last && ($last->estatus_caja ?? null) !== 'RECHAZADO') {
            return redirect()->route('dash.estudiante')->with('err','No puedes enviar otro pago por ahora.');
        }

        $data = $request->validate([
            'referencia'  => ['required','regex:/^\d{20,30}$/'],
            'comprobante' => ['required','file','mimes:pdf,jpg,jpeg,png','max:5120'],
        ]);

        $path = $request->file('comprobante')->store('comprobantes', 'public');

        $pago = $estu->pagos()->create([
            'referencia'        => $data['referencia'],
            'comprobante_path'  => $path,
            'estatus_caja'      => 'PENDIENTE',
            'estatus_admin'     => 'PENDIENTE',
        ]);

        // Notificar a CAJA y al ESTUDIANTE
        Notificacion::toRole('CAJA', 'Nuevo pago para validar', "Matrícula {$estu->matricula}: pago #{$pago->id} enviado.");
        Notificacion::toUser(auth()->id(), 'Pago enviado', "Tu pago #{$pago->id} fue enviado a Caja.");

        return redirect()->route('dash.estudiante')->with('ok','Pago enviado a Caja.');
    }
}
