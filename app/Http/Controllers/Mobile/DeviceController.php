<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\{DeviceToken,Estudiante,Pago,Reinscripcion};

class DeviceController extends Controller
{
    // Guarda/actualiza token del dispositivo del usuario autenticado
    public function register(Request $request)
    {
        $data = $request->validate([
            'token'    => 'required|string|max:255',
            'platform' => 'nullable|string|max:20', // android|ios|web
        ]);

        $userId = $request->user()->id;

        DeviceToken::updateOrCreate(
            ['user_id' => $userId, 'token' => $data['token']],
            ['platform' => $data['platform'] ?? null, 'last_seen_at' => now()]
        );

        return response()->json(['ok' => true]);
    }

    // Estado sintetizado del alumno autenticado
    public function estado(Request $request)
    {
        $user = $request->user();
        $estu = Estudiante::where('user_id', $user->id)->first();

        if (!$estu) {
            return response()->json(['ok'=>true,'estado'=>'NO_INICIADO','mensaje'=>'No se encontró ficha de estudiante.']);
        }

        $perfilEnviado = (bool)($estu->validado_en) || (bool) data_get($estu,'validado_datos',false) || (bool) session('perfil_enviado', false);

        $pago = Pago::where('estudiante_id',$estu->id)->latest()->first();
        $rein = Reinscripcion::where('estudiante_id',$estu->id)->latest()->first();

        if (!$perfilEnviado) {
            return ['ok'=>true,'estado'=>'NO_INICIADO','mensaje'=>'Aún no inicias tu proceso. Realiza tu validación de datos.'];
        }

        if ($rein && ($rein->estatus_final === 'APROBADA')) {
            return ['ok'=>true,'estado'=>'REINSCRITO','mensaje'=>'¡Tu reinscripción fue aprobada!'];
        }

        if ($rein && ($rein->estatus_final === 'RECHAZADA')) {
            return ['ok'=>true,'estado'=>'RECHAZADA','mensaje'=>'Tu proceso fue rechazado por el administrador.'];
        }

        // Si llegó aquí, está en curso
        $msg = 'Tu proceso está en revisión.';
        if ($pago) {
            if (($pago->estatus_caja ?? null) === 'RECHAZADO') {
                $msg = 'Caja rechazó tu pago: '.($pago->observaciones_caja ?? 'Motivo no especificado').'. Corrige y reintenta.';
            } elseif (($pago->estatus_caja ?? null) === 'VALIDADO') {
                $msg = 'Pago validado por Caja. Espera la aprobación final.';
            } else {
                $msg = 'Pago enviado. Caja está revisando.';
            }
        }
        return ['ok'=>true,'estado'=>'EN_PROCESO','mensaje'=>$msg];
    }
}
