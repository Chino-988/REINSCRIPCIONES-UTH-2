<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // Agrega la columna si aún no existe
        if (!Schema::hasColumn('users', 'role')) {
            Schema::table('users', function (Blueprint $table) {
                // Usamos string para máxima compatibilidad con Postgres
                $table->string('role', 20)->default('ESTUDIANTE');
            });

            // Asegura valor por defecto en filas existentes
            DB::table('users')->whereNull('role')->update(['role' => 'ESTUDIANTE']);
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'role')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('role');
            });
        }
    }
};
