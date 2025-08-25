<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'pgsql') {
            // En tu BD el constraint se llama 'pagos_estatus_caja_check'
            DB::statement('ALTER TABLE pagos DROP CONSTRAINT IF EXISTS pagos_estatus_caja_check;');
            DB::statement("
                ALTER TABLE pagos
                ADD CONSTRAINT pagos_estatus_caja_check
                CHECK (
                    estatus_caja IN ('PENDIENTE','VALIDADO','RECHAZADO','EN_PROCESO')
                    OR estatus_caja IS NULL
                );
            ");
        } elseif ($driver === 'mysql') {
            // Por si algún entorno usa MySQL con ENUM
            DB::statement("
                ALTER TABLE pagos
                MODIFY estatus_caja ENUM('PENDIENTE','VALIDADO','RECHAZADO','EN_PROCESO') NULL
            ");
        }
    }

    public function down(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE pagos DROP CONSTRAINT IF EXISTS pagos_estatus_caja_check;');
            DB::statement("
                ALTER TABLE pagos
                ADD CONSTRAINT pagos_estatus_caja_check
                CHECK (
                    estatus_caja IN ('PENDIENTE','VALIDADO','RECHAZADO')
                    OR estatus_caja IS NULL
                );
            ");
        } elseif ($driver === 'mysql') {
            DB::statement("
                ALTER TABLE pagos
                MODIFY estatus_caja ENUM('PENDIENTE','VALIDADO','RECHAZADO') NULL
            ");
        }
    }
};
