<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('condiciones_funcionales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('estudiante_id')->constrained('estudiantes')->cascadeOnDelete();

            $table->boolean('ninguna')->default(false);
            $table->boolean('estar_de_pie_mareo')->default(false);
            $table->boolean('caminar_sin_ayuda')->default(false);
            $table->boolean('desplazar_problemas')->default(false);
            $table->boolean('manipular_no_dibuja_casa')->default(false);
            $table->boolean('hablar_no_solicita_ayuda')->default(false);
            $table->boolean('postura_pierde_fuerza')->default(false);
            $table->boolean('otras_acciones_no_deporte')->default(false);
            $table->boolean('oido_izq_oigo_poco')->default(false);
            $table->boolean('oido_der_oigo_poco')->default(false);
            $table->boolean('oido_izq_no_oigo')->default(false);
            $table->boolean('oido_der_no_oigo')->default(false);
            $table->boolean('ojo_izq_casi_no_ve')->default(false);
            $table->boolean('ojo_der_casi_no_ve')->default(false);
            $table->boolean('ojo_izq_no_ve')->default(false);
            $table->boolean('ojo_der_no_ve')->default(false);
            $table->boolean('tarda_comprender_lectura')->default(false);
            $table->boolean('no_entiende_lectura')->default(false);
            $table->boolean('escritura_no_entendible')->default(false);
            $table->boolean('dificultad_lect_escr_mapa')->default(false);
            $table->boolean('dificultad_matematicas_basicas')->default(false);
            $table->boolean('olvida_datos_personales')->default(false);
            $table->boolean('dificultad_interactuar')->default(false);
            $table->boolean('dificultad_establecer_platica')->default(false);
            $table->boolean('prefiere_solo')->default(false);
            $table->boolean('prefiere_trabajar_solo')->default(false);
            $table->boolean('escucha_voces')->default(false);
            $table->boolean('ve_personas_objetos')->default(false);
            $table->boolean('cambios_estado_animo')->default(false);
            $table->boolean('enfermedad_nacimiento')->default(false);
            $table->boolean('enfermedad_cronica')->default(false);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('condiciones_funcionales');
    }
};
