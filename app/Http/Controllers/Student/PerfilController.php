<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Carbon;
use App\Models\{Estudiante, CondicionFuncional, Reinscripcion, Notificacion};

class PerfilController extends Controller
{
    public function edit()
    {
        $estu = Estudiante::where('user_id', auth()->id())
            ->with(['condicionesFuncionales']) // precarga la hasOne real
            ->firstOrFail();

        $rein = Reinscripcion::where('estudiante_id',$estu->id)->latest()->first();

        // BLOQUEO: si ya envió validación y Admin no ha rechazado, no permite editar
        $perfilEnviado = (bool)($estu->validado_en) || (bool) data_get($estu, 'validado_datos', false);
        if ($perfilEnviado && (!$rein || $rein->estatus_final !== 'RECHAZADA')) {
            return redirect()->route('dash.estudiante')
                ->with('info','Tus datos están en revisión. Si el Admin rechaza, podrás editarlos de nuevo.');
        }

        $cond = $estu->condicionesFuncionales; // relación hasOne (puede venir null)
        $catalogoCondiciones = Estudiante::catalogoCondicionesFuncionales();

        return view('estudiante.perfil', compact('estu','cond','catalogoCondiciones'));
    }

    public function update(Request $request)
    {
        $estu = Estudiante::where('user_id', auth()->id())->firstOrFail();
        $rein = Reinscripcion::where('estudiante_id',$estu->id)->latest()->first();

        // BLOQUEO server-side
        $perfilEnviado = (bool)($estu->validado_en) || (bool) data_get($estu,'validado_datos',false);
        if ($perfilEnviado && (!$rein || $rein->estatus_final !== 'RECHAZADA')) {
            return redirect()->route('dash.estudiante')->with('err','No puedes editar mientras está en revisión.');
        }

        $request->validate([
            'nombre'              => 'required|string|max:100',
            'apellido_paterno'    => 'required|string|max:100',
            'apellido_materno'    => 'nullable|string|max:100',
            'pertenencia_etnica'  => 'required|string|max:50',
            'lengua_indigena'     => 'nullable|string|max:100',
            'ingreso_mensual'     => 'nullable|numeric|min:0',
            'dependientes'        => 'nullable|integer|min:0',
            'estado_civil'        => 'nullable|string|max:50',
            'acepta_declaracion'  => 'accepted',

            // Comunicación
            'correo_institucional'=> 'nullable|email|max:150',
            'correo_personal'     => 'nullable|email|max:150',
            'tel_movil'           => 'nullable|string|max:30',
            'tel_padre'           => 'nullable|string|max:30',
            'tel_madre'           => 'nullable|string|max:30',
            'direccion'           => 'nullable|string|max:1000',

            // Catálogo (si la columna existe, se guardará)
            'condicion_funcional' => 'nullable|in:'.implode(',', array_keys(Estudiante::catalogoCondicionesFuncionales())),
        ], [
            'condicion_funcional.in' => 'La condición seleccionada no es válida.',
        ]);

        // Datos generales
        $estu->fill([
            'nombre'             => $request->input('nombre'),
            'apellido_paterno'   => $request->input('apellido_paterno'),
            'apellido_materno'   => $request->input('apellido_materno'),
            'pertenencia_etnica' => $request->input('pertenencia_etnica'),
            'lengua_indigena'    => $request->input('lengua_indigena'),
            'ingreso_mensual'    => $request->input('ingreso_mensual'),
            'dependientes'       => $request->input('dependientes'),
            'estado_civil'       => $request->input('estado_civil'),
            'acepta_declaracion' => $request->boolean('acepta_declaracion'),
        ]);

        // Guardar select de "condicion_funcional" solo si existe la columna
        if (Schema::hasColumn('estudiantes','condicion_funcional')) {
            $estu->condicion_funcional = $request->input('condicion_funcional') ?: null;
        }

        // Comunicación — se guarda como arrays (JSON)
        $tels = [];
        if ($request->filled('tel_movil')) $tels[] = ['tipo'=>'Teléfono móvil','dato'=>$request->input('tel_movil'),'comentario'=>'Estudiante'];
        if ($request->filled('tel_padre')) $tels[] = ['tipo'=>'Teléfono móvil','dato'=>$request->input('tel_padre'),'comentario'=>'Padre'];
        if ($request->filled('tel_madre')) $tels[] = ['tipo'=>'Teléfono móvil','dato'=>$request->input('tel_madre'),'comentario'=>'Madre'];

        $correos = [];
        if ($request->filled('correo_institucional')) $correos[] = ['tipo'=>'Correo institucional','dato'=>$request->input('correo_institucional'),'comentario'=>'Institucional'];
        if ($request->filled('correo_personal'))      $correos[] = ['tipo'=>'Correo personal','dato'=>$request->input('correo_personal'),'comentario'=>'Personal'];

        $domicilios = [];
        if ($request->filled('direccion')) $domicilios[] = ['direccion'=>$request->input('direccion'),'comentario'=>'Principal'];

        $estu->telefonos  = $tels;
        $estu->correos    = $correos;
        $estu->domicilios = $domicilios;

        // Marca envío para revisión (bloqueo)
        $estu->validado_datos = true;
        $estu->validado_en    = Carbon::now();
        $estu->save();

        // Condiciones funcionales (checkboxes) — relación HasOne real
        $fields = [
            'ninguna','estar_de_pie_mareo','caminar_sin_ayuda','desplazar_problemas',
            'manipular_no_dibuja_casa','hablar_no_solicita_ayuda','postura_pierde_fuerza',
            'otras_acciones_no_deporte','oido_izq_oigo_poco','oido_der_oigo_poco','oido_izq_no_oigo','oido_der_no_oigo',
            'ojo_izq_casi_no_ve','ojo_der_casi_no_ve','ojo_izq_no_ve','ojo_der_no_ve','tarda_comprender_lectura',
            'no_entiende_lectura','escritura_no_entendible','dificultad_lect_escr_mapa','dificultad_matematicas_basicas',
            'olvida_datos_personales','dificultad_interactuar','dificultad_establecer_platica','prefiere_solo',
            'prefiere_trabajar_solo','escucha_voces','ve_personas_objetos','cambios_estado_animo','enfermedad_nacimiento','enfermedad_cronica',
        ];

        $cond = $estu->condicionesFuncionales()->firstOrNew([]);
        $ninguna = $request->boolean('ninguna');

        foreach ($fields as $f) {
            $cond->$f = $f === 'ninguna'
                ? $ninguna
                : ($ninguna ? false : $request->has($f));
        }
        $cond->estudiante_id = $estu->id;
        $cond->save();

        // Bandera de sesión para que el Dashboard cambie de inmediato
        session(['perfil_enviado' => true]);

        // Notificar a ADMIN
        Notificacion::toRole('ADMIN', 'Estudiante validó datos', "Matrícula {$estu->matricula}: datos enviados para revisión.");

        return redirect()->route('dash.estudiante')->with('ok', 'Datos enviados. Quedan en revisión.');
    }
}
