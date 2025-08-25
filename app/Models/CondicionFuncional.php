<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CondicionFuncional extends Model
{
    protected $table = 'condiciones_funcionales';

    protected $fillable = [
        'estudiante_id','ninguna','estar_de_pie_mareo','caminar_sin_ayuda','desplazar_problemas',
        'manipular_no_dibuja_casa','hablar_no_solicita_ayuda','postura_pierde_fuerza',
        'otras_acciones_no_deporte','oido_izq_oigo_poco','oido_der_oigo_poco','oido_izq_no_oigo',
        'oido_der_no_oigo','ojo_izq_casi_no_ve','ojo_der_casi_no_ve','ojo_izq_no_ve','ojo_der_no_ve',
        'tarda_comprender_lectura','no_entiende_lectura','escritura_no_entendible',
        'dificultad_lect_escr_mapa','dificultad_matematicas_basicas','olvida_datos_personales',
        'dificultad_interactuar','dificultad_establecer_platica','prefiere_solo','prefiere_trabajar_solo',
        'escucha_voces','ve_personas_objetos','cambios_estado_animo','enfermedad_nacimiento','enfermedad_cronica',
    ];

    protected $casts = [
        'ninguna' => 'boolean',
        'estar_de_pie_mareo' => 'boolean','caminar_sin_ayuda' => 'boolean','desplazar_problemas' => 'boolean',
        'manipular_no_dibuja_casa' => 'boolean','hablar_no_solicita_ayuda' => 'boolean','postura_pierde_fuerza' => 'boolean',
        'otras_acciones_no_deporte' => 'boolean','oido_izq_oigo_poco' => 'boolean','oido_der_oigo_poco' => 'boolean',
        'oido_izq_no_oigo' => 'boolean','oido_der_no_oigo' => 'boolean','ojo_izq_casi_no_ve' => 'boolean','ojo_der_casi_no_ve' => 'boolean',
        'ojo_izq_no_ve' => 'boolean','ojo_der_no_ve' => 'boolean','tarda_comprender_lectura' => 'boolean','no_entiende_lectura' => 'boolean',
        'escritura_no_entendible' => 'boolean','dificultad_lect_escr_mapa' => 'boolean','dificultad_matematicas_basicas' => 'boolean',
        'olvida_datos_personales' => 'boolean','dificultad_interactuar' => 'boolean','dificultad_establecer_platica' => 'boolean',
        'prefiere_solo' => 'boolean','prefiere_trabajar_solo' => 'boolean','escucha_voces' => 'boolean','ve_personas_objetos' => 'boolean',
        'cambios_estado_animo' => 'boolean','enfermedad_nacimiento' => 'boolean','enfermedad_cronica' => 'boolean',
    ];

    public function estudiante() { return $this->belongsTo(Estudiante::class); }
}
