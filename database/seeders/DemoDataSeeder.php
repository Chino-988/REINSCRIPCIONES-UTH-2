<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\Estudiante;
use App\Models\Pago;
use App\Models\Reinscripcion;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        // ===== Helpers =====
        $genRef = function (int $len = 20): string {
            $s = '';
            for ($i=0; $i<$len; $i++) { $s .= random_int(0,9); }
            return $s;
        };

        // Crea 12 estudiantes de prueba con distintos estados de pago
        // 1-5   : pago PENDIENTE (para Caja)
        // 6-9   : pago VALIDADO por Caja, Admin PENDIENTE (para Admin)
        // 10-11 : pago RECHAZADO por Caja
        // 12-13 : reinscripción APROBADA (ya aprobadas)
        // 14    : reinscripción RECHAZADA
        $total = 14;

        for ($i=1; $i<=$total; $i++) {
            $email = sprintf('alumno%02d@uth.edu.mx', $i);

            $user = User::updateOrCreate(
                ['email' => $email],
                [
                    'name' => 'Alumno '.$i,
                    'role' => 'ESTUDIANTE',
                    'password' => Hash::make('password'),
                ]
            );

            $est = Estudiante::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'matricula' => sprintf('3522%06d', $i),
                    'nombre' => 'Alumno'.$i,
                    'apellido_paterno' => 'Demo',
                    'apellido_materno' => 'UTH',
                    'correo_institucional' => $email,
                    'telefono_movil' => '222000'.sprintf('%04d', $i),
                    'direccion' => 'Calle Demo #'.$i.', Huejotzingo, Puebla',
                ]
            );

            // Estado del pago según bloque
            if ($i <= 5) {
                // PENDIENTE (Caja)
                $pago = Pago::updateOrCreate(
                    ['estudiante_id' => $est->id, 'referencia' => $genRef()],
                    [
                        'estatus_caja' => 'PENDIENTE',
                        'estatus_admin' => 'PENDIENTE',
                        'observaciones_caja' => null,
                        'observaciones_admin' => null,
                        'comprobante_path' => null,
                    ]
                );
                // Reinscripción aún en proceso
                Reinscripcion::updateOrCreate(
                    ['estudiante_id' => $est->id],
                    ['pago_id' => $pago->id, 'estatus_final' => 'EN_PROCESO']
                );

            } elseif ($i <= 9) {
                // VALIDADO por Caja, Admin PENDIENTE
                $pago = Pago::updateOrCreate(
                    ['estudiante_id' => $est->id, 'referencia' => $genRef()],
                    [
                        'estatus_caja' => 'VALIDADO',
                        'estatus_admin' => 'PENDIENTE',
                        'observaciones_caja' => 'OK Caja',
                        'observaciones_admin' => null,
                        'comprobante_path' => null,
                    ]
                );
                Reinscripcion::updateOrCreate(
                    ['estudiante_id' => $est->id],
                    ['pago_id' => $pago->id, 'estatus_final' => 'EN_PROCESO']
                );

            } elseif ($i <= 11) {
                // RECHAZADO por Caja
                $pago = Pago::updateOrCreate(
                    ['estudiante_id' => $est->id, 'referencia' => $genRef()],
                    [
                        'estatus_caja' => 'RECHAZADO',
                        'estatus_admin' => 'PENDIENTE',
                        'observaciones_caja' => 'Referencia ilegible',
                        'observaciones_admin' => null,
                        'comprobante_path' => null,
                    ]
                );
                Reinscripcion::updateOrCreate(
                    ['estudiante_id' => $est->id],
                    ['pago_id' => $pago->id, 'estatus_final' => 'RECHAZADA']
                );

            } elseif ($i <= 13) {
                // APROBADA (ya generada)
                $pago = Pago::updateOrCreate(
                    ['estudiante_id' => $est->id, 'referencia' => $genRef()],
                    [
                        'estatus_caja' => 'VALIDADO',
                        'estatus_admin' => 'VALIDADO',
                        'observaciones_caja' => 'OK Caja',
                        'observaciones_admin' => 'OK Admin',
                        'comprobante_path' => null,
                    ]
                );
                Reinscripcion::updateOrCreate(
                    ['estudiante_id' => $est->id],
                    [
                        'pago_id' => $pago->id,
                        'estatus_final' => 'APROBADA',
                        'constancia_pdf_path' => null,
                        'constancia_qr_path' => null,
                        'token_verificacion' => Str::random(40),
                    ]
                );

            } else {
                // RECHAZADA (final)
                $pago = Pago::updateOrCreate(
                    ['estudiante_id' => $est->id, 'referencia' => $genRef()],
                    [
                        'estatus_caja' => 'VALIDADO',
                        'estatus_admin' => 'RECHAZADO',
                        'observaciones_caja' => 'OK Caja',
                        'observaciones_admin' => 'Doc. incompleto',
                        'comprobante_path' => null,
                    ]
                );
                Reinscripcion::updateOrCreate(
                    ['estudiante_id' => $est->id],
                    [
                        'pago_id' => $pago->id,
                        'estatus_final' => 'RECHAZADA',
                        'constancia_pdf_path' => null,
                        'constancia_qr_path' => null,
                        'token_verificacion' => Str::random(40),
                    ]
                );
            }
        }
    }
}
