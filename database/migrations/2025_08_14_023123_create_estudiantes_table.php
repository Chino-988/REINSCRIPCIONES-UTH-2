<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('estudiantes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('matricula')->unique();
            $table->string('nombre');
            $table->string('apellido_paterno');
            $table->string('apellido_materno')->nullable();
            $table->string('foto_path')->nullable();

            $table->string('pertenece_etnia')->nullable();
            $table->string('lengua_indigena')->nullable();
            $table->text('condiciones_funcionales')->nullable();

            $table->decimal('ingreso_mensual',10,2)->nullable();
            $table->integer('dependientes_economicos')->nullable();
            $table->string('estado_civil')->nullable();

            $table->string('telefono_movil')->nullable();
            $table->string('telefono_padre')->nullable();
            $table->string('telefono_madre')->nullable();
            $table->string('correo_institucional')->nullable();
            $table->string('correo_personal')->nullable();

            $table->string('direccion')->nullable();

            $table->string('tutor_nombre')->nullable();
            $table->string('tutor_apellido_paterno')->nullable();
            $table->string('tutor_apellido_materno')->nullable();
            $table->string('tutor_trato')->nullable();

            $table->boolean('validado_datos')->default(false);
            $table->timestamp('fecha_validacion_datos')->nullable();

            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('estudiantes');
    }
};
