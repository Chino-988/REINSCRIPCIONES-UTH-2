<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Estudiante;
use Illuminate\Http\Request;

class EstadoReinscripcionController extends Controller
{
    /**
     * GET /api/v1/reinscripciones/estado/{matricula?}
     *    o /api/v1/reinscripciones/estado?matricula=XXXX
     *
     * Lógica de negocio:
     * - PROCESO NO INICIADO: antes de que el alumno envíe la Validación de datos.
     * - EN PROCESO DE REINSCRIPCIÓN: al enviar Validación de datos; se mantiene así
     *   aunque la Caja valide o rechace el pago (si hay rechazo mostramos alertas).
     * - REINSCRITO: cuando Admin finaliza (reinscripciones.estatus_final = APROBADA).
     * - RECHAZADO: cuando Admin rechaza (reinscripciones.estatus_final = RECHAZADA).
     */
    public function show(Request $request, ?string $matricula = null)
    {
        $matricula = $matricula ?: (string) $request->query('matricula', '');
        if (trim($matricula) === '') {
            return response()->json(['ok'=>false,'message'=>'Falta parámetro: matrícula'], 422);
        }

        $estudiante = Estudiante::query()
            ->where('matricula', $matricula)
            ->with([
                'pagos' => fn ($q) => $q->latest('id')->limit(1),
                'reinscripcion.pago',
            ])
            ->first();

        if (!$estudiante) {
            return response()->json(['ok'=>false,'message'=>'Estudiante no encontrado'], 404);
        }

        $ultimoPago     = optional($estudiante->pagos)->first();
        $rein           = $estudiante->reinscripcion;
        $perfilEnviado  = (bool)($estudiante->validado_en) || (bool) data_get($estudiante, 'validado_datos', false);

        // Alertas informativas de pago (NO cambian estado global)
        $alertas = [];
        if ($ultimoPago) {
            if (strtoupper((string)$ultimoPago->estatus_caja) === 'RECHAZADO') {
                $alertas[] = 'Pago rechazado por Caja: '.($ultimoPago->observaciones_caja ?: 'Motivo no especificado');
            }
            if (strtoupper((string)$ultimoPago->estatus_admin) === 'RECHAZADO') {
                $alertas[] = 'Pago rechazado por Administración: '.($ultimoPago->observaciones_admin ?: 'Motivo no especificado');
            }
        }

        // 0) PROCESO NO INICIADO (aún no envía Validación de datos)
        if (!$perfilEnviado) {
            return response()->json([
                'ok'      => true,
                'estado'  => 'PROCESO NO INICIADO',
                'mensaje' => 'Completa la Validación de datos para iniciar tu proceso de reinscripción.',
                'alertas' => $alertas,
                'datos'   => [
                    'matricula'               => $estudiante->matricula,
                    'validacion_datos_enviada'=> false,
                    'pago' => $ultimoPago ? [
                        'referencia'    => $ultimoPago->referencia,
                        'estatus_caja'  => $ultimoPago->estatus_caja,
                        'estatus_admin' => $ultimoPago->estatus_admin,
                    ] : null,
                ],
            ]);
        }

        // 1) REINSCRITO (final admin + constancia)
        if ($rein && strtoupper((string)$rein->estatus_final) === 'APROBADA') {
            return response()->json([
                'ok'      => true,
                'estado'  => 'REINSCRITO',
                'mensaje' => 'Su reinscripción se ha completado satisfactoriamente.',
                'alertas' => $alertas,
                'datos'   => [
                    'matricula'               => $estudiante->matricula,
                    'validacion_datos_enviada'=> true,
                    'token'                   => $rein->token_verificacion ?? null,
                    'constancia_pdf'          => $rein->constancia_pdf_path ?? null,
                ],
            ]);
        }

        // 2) RECHAZADO (final admin)
        if ($rein && strtoupper((string)$rein->estatus_final) === 'RECHAZADA') {
            $motivo = ($ultimoPago->observaciones_admin ?? null)
                   ?: ($ultimoPago->observaciones_caja ?? null)
                   ?: 'Sin motivo especificado';
            return response()->json([
                'ok'      => true,
                'estado'  => 'RECHAZADO',
                'mensaje' => 'Tu reinscripción fue rechazada. Motivo: '.$motivo,
                'alertas' => $alertas,
                'datos'   => [
                    'matricula'               => $estudiante->matricula,
                    'validacion_datos_enviada'=> true,
                    'pago' => $ultimoPago ? [
                        'referencia'    => $ultimoPago->referencia,
                        'estatus_caja'  => $ultimoPago->estatus_caja,
                        'estatus_admin' => $ultimoPago->estatus_admin,
                    ] : null,
                ],
            ]);
        }

        // 3) EN PROCESO (perfil enviado, admin aún no finaliza)
        return response()->json([
            'ok'      => true,
            'estado'  => 'EN PROCESO DE REINSCRIPCIÓN',
            'mensaje' => 'Tu trámite continúa en proceso. La validación final la realiza Administración.',
            'alertas' => $alertas,
            'datos'   => [
                'matricula'               => $estudiante->matricula,
                'validacion_datos_enviada'=> true,
                'pago' => $ultimoPago ? [
                    'referencia'    => $ultimoPago->referencia,
                    'estatus_caja'  => $ultimoPago->estatus_caja,
                    'estatus_admin' => $ultimoPago->estatus_admin,
                ] : null,
            ],
        ]);
    }
}
