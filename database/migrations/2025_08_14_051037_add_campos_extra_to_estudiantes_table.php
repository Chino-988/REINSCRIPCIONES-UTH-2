<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('estudiantes', function (Blueprint $table) {
            if (!Schema::hasColumn('estudiantes', 'foto_path')) $table->string('foto_path')->nullable();

            if (!Schema::hasColumn('estudiantes', 'pertenencia_etnica')) $table->string('pertenencia_etnica', 50)->nullable()->default('Ninguna');
            if (!Schema::hasColumn('estudiantes', 'lengua_indigena'))   $table->string('lengua_indigena', 50)->nullable()->default('Ninguna');

            if (!Schema::hasColumn('estudiantes', 'ingreso_mensual'))   $table->decimal('ingreso_mensual', 10, 2)->nullable();
            if (!Schema::hasColumn('estudiantes', 'dependientes'))      $table->integer('dependientes')->nullable();
            if (!Schema::hasColumn('estudiantes', 'estado_civil'))      $table->string('estado_civil', 30)->nullable()->default('Soltero(a)');

            if (!Schema::hasColumn('estudiantes', 'telefonos'))         $table->jsonb('telefonos')->nullable();
            if (!Schema::hasColumn('estudiantes', 'correos'))           $table->jsonb('correos')->nullable();
            if (!Schema::hasColumn('estudiantes', 'domicilios'))        $table->jsonb('domicilios')->nullable();

            if (!Schema::hasColumn('estudiantes', 'tutor_trato'))       $table->string('tutor_trato', 10)->nullable(); // Sr. / Sra.
            if (!Schema::hasColumn('estudiantes', 'tutor_nombre'))      $table->string('tutor_nombre', 80)->nullable();
            if (!Schema::hasColumn('estudiantes', 'tutor_apellido_paterno')) $table->string('tutor_apellido_paterno', 80)->nullable();
            if (!Schema::hasColumn('estudiantes', 'tutor_apellido_materno')) $table->string('tutor_apellido_materno', 80)->nullable();

            if (!Schema::hasColumn('estudiantes', 'acepta_declaracion')) $table->boolean('acepta_declaracion')->default(false);
            if (!Schema::hasColumn('estudiantes', 'validado_en'))        $table->timestamp('validado_en')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('estudiantes', function (Blueprint $table) {
            $cols = [
                'foto_path','pertenencia_etnica','lengua_indigena','ingreso_mensual','dependientes',
                'estado_civil','telefonos','correos','domicilios','tutor_trato','tutor_nombre',
                'tutor_apellido_paterno','tutor_apellido_materno','acepta_declaracion','validado_en'
            ];
            foreach ($cols as $c) { if (Schema::hasColumn('estudiantes', $c)) $table->dropColumn($c); }
        });
    }
};
