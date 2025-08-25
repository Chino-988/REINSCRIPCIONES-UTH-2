<x-app-layout>
  <x-slot name="header">Validación de datos</x-slot>

  <div class="space-y-6">
    @if (session('ok'))  <div class="p-3 bg-emerald-600 text-white rounded">{{ session('ok') }}</div> @endif
    @if (session('err')) <div class="p-3 bg-rose-600 text-white rounded">{{ session('err') }}</div> @endif
    @if (session('info'))<div class="p-3 bg-blue-600 text-white rounded">{{ session('info') }}</div> @endif

    @if ($errors->any())
      <div class="p-3 bg-rose-50 border border-rose-200 rounded text-rose-800">
        <b>Hay errores en el formulario:</b>
        <ul class="list-disc ml-6 mt-1">
          @foreach ($errors->all() as $e)
            <li>{{ $e }}</li>
          @endforeach
        </ul>
      </div>
    @endif

    @php
      $cond = $cond ?? ($estu->condicionesFuncionales ?? null);
      $catalogoCondiciones = $catalogoCondiciones ?? \App\Models\Estudiante::catalogoCondicionesFuncionales();
    @endphp

    <form method="POST" action="{{ route('est.perfil.update') }}">
      @csrf

      {{-- Datos generales --}}
      <div class="card">
        <div class="card-title">Datos generales</div>
        <div class="grid md:grid-cols-3 gap-4 mt-3">
          <div>
            <label class="block text-sm font-medium">Nombre *</label>
            <input type="text" name="nombre" class="input" value="{{ old('nombre', $estu->nombre) }}" required>
          </div>
          <div>
            <label class="block text-sm font-medium">Apellido paterno *</label>
            <input type="text" name="apellido_paterno" class="input" value="{{ old('apellido_paterno', $estu->apellido_paterno) }}" required>
          </div>
          <div>
            <label class="block text-sm font-medium">Apellido materno</label>
            <input type="text" name="apellido_materno" class="input" value="{{ old('apellido_materno', $estu->apellido_materno) }}">
          </div>

          <div>
            <label class="block text-sm font-medium">Pertenencia étnica *</label>
            <input type="text" name="pertenencia_etnica" class="input" value="{{ old('pertenencia_etnica', $estu->pertenencia_etnica) }}" required>
          </div>
          <div>
            <label class="block text-sm font-medium">Lengua indígena</label>
            <input type="text" name="lengua_indigena" class="input" value="{{ old('lengua_indigena', $estu->lengua_indigena) }}">
          </div>
          <div>
            <label class="block text-sm font-medium">Estado civil</label>
            <input type="text" name="estado_civil" class="input" value="{{ old('estado_civil', $estu->estado_civil) }}">
          </div>

          <div>
            <label class="block text-sm font-medium">Ingreso mensual</label>
            <input type="number" step="0.01" min="0" name="ingreso_mensual" class="input" value="{{ old('ingreso_mensual', $estu->ingreso_mensual) }}">
          </div>
          <div>
            <label class="block text-sm font-medium">Dependientes</label>
            <input type="number" min="0" name="dependientes" class="input" value="{{ old('dependientes', $estu->dependientes) }}">
          </div>

          {{-- Condición funcional (catálogo) --}}
          <div>
            <label class="block text-sm font-medium">Condición funcional</label>
            <select name="condicion_funcional" class="input">
              <option value="">— Selecciona —</option>
              @foreach($catalogoCondiciones as $key => $label)
                <option value="{{ $key }}" @selected(old('condicion_funcional', $estu->condicion_funcional) === $key)>
                  {{ $label }}
                </option>
              @endforeach
            </select>
            <p class="text-xs text-gray-500 mt-1">Si seleccionas “Ninguna”, puedes omitir los checkboxes de abajo.</p>
          </div>
        </div>
      </div>

      {{-- Comunicación --}}
      <div class="card">
        <div class="card-title">Comunicación</div>
        <div class="grid md:grid-cols-3 gap-4 mt-3">
          <div>
            <label class="block text-sm font-medium">Correo institucional</label>
            <input type="email" name="correo_institucional" class="input" value="{{ old('correo_institucional') }}">
          </div>
          <div>
            <label class="block text-sm font-medium">Correo personal</label>
            <input type="email" name="correo_personal" class="input" value="{{ old('correo_personal') }}">
          </div>
          <div>
            <label class="block text-sm font-medium">Teléfono móvil</label>
            <input type="text" name="tel_movil" class="input" value="{{ old('tel_movil') }}">
          </div>
          <div>
            <label class="block text-sm font-medium">Teléfono padre</label>
            <input type="text" name="tel_padre" class="input" value="{{ old('tel_padre') }}">
          </div>
          <div>
            <label class="block text-sm font-medium">Teléfono madre</label>
            <input type="text" name="tel_madre" class="input" value="{{ old('tel_madre') }}">
          </div>
          <div class="md:col-span-3">
            <label class="block text-sm font-medium">Dirección</label>
            <textarea name="direccion" class="input" rows="2">{{ old('direccion') }}</textarea>
          </div>
        </div>
      </div>

      {{-- Condiciones funcionales (checkboxes) --}}
      <div class="card">
        <div class="card-title">Condiciones funcionales (detalle)</div>
        <p class="text-sm text-gray-600">Marca las que apliquen. Si seleccionas “Ninguna”, las demás se desactivarán.</p>

        @php
          $isTrue = function($name) use ($cond){
            $old = old($name, null);
            if(!is_null($old)) return (bool)$old;
            return $cond ? (bool) data_get($cond, $name, false) : false;
          };
        @endphp

        <div class="mt-3 grid md:grid-cols-3 gap-2">
          <label class="inline-flex items-center gap-2">
            <input type="checkbox" name="ninguna" value="1" @checked($isTrue('ninguna'))>
            <span>Ninguna</span>
          </label>

          <label class="inline-flex items-center gap-2">
            <input type="checkbox" name="estar_de_pie_mareo" @checked($isTrue('estar_de_pie_mareo'))>
            <span>Se marea al ponerse de pie</span>
          </label>
          <label class="inline-flex items-center gap-2">
            <input type="checkbox" name="caminar_sin_ayuda" @checked($isTrue('caminar_sin_ayuda'))>
            <span>Dificultad para caminar sin ayuda</span>
          </label>
          <label class="inline-flex items-center gap-2">
            <input type="checkbox" name="desplazar_problemas" @checked($isTrue('desplazar_problemas'))>
            <span>Problemas para desplazarse</span>
          </label>
          <label class="inline-flex items-center gap-2">
            <input type="checkbox" name="postura_pierde_fuerza" @checked($isTrue('postura_pierde_fuerza'))>
            <span>Pierde fuerza o postura</span>
          </label>
          <label class="inline-flex items-center gap-2 md:col-span-2">
            <input type="checkbox" name="otras_acciones_no_deporte" @checked($isTrue('otras_acciones_no_deporte'))>
            <span>Otras acciones (no deporte)</span>
          </label>

          <label class="inline-flex items-center gap-2">
            <input type="checkbox" name="manipular_no_dibuja_casa" @checked($isTrue('manipular_no_dibuja_casa'))>
            <span>Dificultad para manipular / dibujar</span>
          </label>
          <label class="inline-flex items-center gap-2">
            <input type="checkbox" name="hablar_no_solicita_ayuda" @checked($isTrue('hablar_no_solicita_ayuda'))>
            <span>Dificultad para solicitar ayuda</span>
          </label>

          {{-- Auditiva --}}
          <label class="inline-flex items-center gap-2">
            <input type="checkbox" name="oido_izq_oigo_poco" @checked($isTrue('oido_izq_oigo_poco'))>
            <span>Oído izq. oigo poco</span>
          </label>
          <label class="inline-flex items-center gap-2">
            <input type="checkbox" name="oido_der_oigo_poco" @checked($isTrue('oido_der_oigo_poco'))>
            <span>Oído der. oigo poco</span>
          </label>
          <label class="inline-flex items-center gap-2">
            <input type="checkbox" name="oido_izq_no_oigo" @checked($isTrue('oido_izq_no_oigo'))>
            <span>Oído izq. no oigo</span>
          </label>
          <label class="inline-flex items-center gap-2">
            <input type="checkbox" name="oido_der_no_oigo" @checked($isTrue('oido_der_no_oigo'))>
            <span>Oído der. no oigo</span>
          </label>

          {{-- Visual --}}
          <label class="inline-flex items-center gap-2">
            <input type="checkbox" name="ojo_izq_casi_no_ve" @checked($isTrue('ojo_izq_casi_no_ve'))>
            <span>Ojo izq. casi no ve</span>
          </label>
          <label class="inline-flex items-center gap-2">
            <input type="checkbox" name="ojo_der_casi_no_ve" @checked($isTrue('ojo_der_casi_no_ve'))>
            <span>Ojo der. casi no ve</span>
          </label>
          <label class="inline-flex items-center gap-2">
            <input type="checkbox" name="ojo_izq_no_ve" @checked($isTrue('ojo_izq_no_ve'))>
            <span>Ojo izq. no ve</span>
          </label>
          <label class="inline-flex items-center gap-2">
            <input type="checkbox" name="ojo_der_no_ve" @checked($isTrue('ojo_der_no_ve'))>
            <span>Ojo der. no ve</span>
          </label>

          {{-- Cognitiva / aprendizaje --}}
          <label class="inline-flex items-center gap-2 md:col-span-2">
            <input type="checkbox" name="tarda_comprender_lectura" @checked($isTrue('tarda_comprender_lectura'))>
            <span>Tarda en comprender lecturas</span>
          </label>
          <label class="inline-flex items-center gap-2 md:col-span-2">
            <input type="checkbox" name="no_entiende_lectura" @checked($isTrue('no_entiende_lectura'))>
            <span>No entiende algunas lecturas</span>
          </label>
          <label class="inline-flex items-center gap-2 md:col-span-2">
            <input type="checkbox" name="escritura_no_entendible" @checked($isTrue('escritura_no_entendible'))>
            <span>Escritura poco entendible</span>
          </label>
          <label class="inline-flex items-center gap-2 md:col-span-2">
            <input type="checkbox" name="dificultad_lect_escr_mapa" @checked($isTrue('dificultad_lect_escr_mapa'))>
            <span>Dificultad con lectura/escritura/mapas</span>
          </label>
          <label class="inline-flex items-center gap-2 md:col-span-2">
            <input type="checkbox" name="dificultad_matematicas_basicas" @checked($isTrue('dificultad_matematicas_basicas'))>
            <span>Dificultad en matemáticas básicas</span>
          </label>
          <label class="inline-flex items-center gap-2 md:col-span-2">
            <input type="checkbox" name="olvida_datos_personales" @checked($isTrue('olvida_datos_personales'))>
            <span>Olvida datos personales</span>
          </label>

          {{-- Psicosocial --}}
          <label class="inline-flex items-center gap-2">
            <input type="checkbox" name="dificultad_interactuar" @checked($isTrue('dificultad_interactuar'))>
            <span>Dificultad para interactuar</span>
          </label>
          <label class="inline-flex items-center gap-2">
            <input type="checkbox" name="dificultad_establecer_platica" @checked($isTrue('dificultad_establecer_platica'))>
            <span>Dificultad para establecer plática</span>
          </label>
          <label class="inline-flex items-center gap-2">
            <input type="checkbox" name="prefiere_solo" @checked($isTrue('prefiere_solo'))>
            <span>Prefiere estar solo</span>
          </label>
          <label class="inline-flex items-center gap-2">
            <input type="checkbox" name="prefiere_trabajar_solo" @checked($isTrue('prefiere_trabajar_solo'))>
            <span>Prefiere trabajar solo</span>
          </label>
          <label class="inline-flex items-center gap-2">
            <input type="checkbox" name="escucha_voces" @checked($isTrue('escucha_voces'))>
            <span>Escucha voces</span>
          </label>
          <label class="inline-flex items-center gap-2">
            <input type="checkbox" name="ve_personas_objetos" @checked($isTrue('ve_personas_objetos'))>
            <span>Ve personas/objetos que no están</span>
          </label>
          <label class="inline-flex items-center gap-2 md:col-span-2">
            <input type="checkbox" name="cambios_estado_animo" @checked($isTrue('cambios_estado_animo'))>
            <span>Cambios de estado de ánimo marcados</span>
          </label>

          {{-- Médica --}}
          <label class="inline-flex items-center gap-2">
            <input type="checkbox" name="enfermedad_nacimiento" @checked($isTrue('enfermedad_nacimiento'))>
            <span>Enfermedad de nacimiento</span>
          </label>
          <label class="inline-flex items-center gap-2">
            <input type="checkbox" name="enfermedad_cronica" @checked($isTrue('enfermedad_cronica'))>
            <span>Enfermedad crónica</span>
          </label>
        </div>
      </div>

      {{-- Declaración y envío --}}
      <div class="card">
        <div class="flex items-start gap-2">
          <input type="checkbox" id="acepta_declaracion" name="acepta_declaracion" value="1" class="mt-1" required>
          <label for="acepta_declaracion" class="text-sm">
            Declaro que la información proporcionada es verdadera y autorizo su uso para
            fines de mi proceso de reinscripción.
          </label>
        </div>

        <div class="mt-4 flex gap-3">
          <a href="{{ route('dash.estudiante') }}" class="btn btn-secondary">Cancelar</a>
          <button class="btn btn-primary" type="submit">Enviar validación</button>
        </div>
      </div>
    </form>
  </div>

  {{-- UX: si marca “Ninguna”, desactiva el resto de checkboxes --}}
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const none = document.querySelector('input[name="ninguna"]');
      const others = Array.from(document.querySelectorAll('input[type="checkbox"]'))
        .filter(i => i.name !== 'ninguna');

      const refresh = () => {
        const off = none.checked;
        others.forEach(i => { i.disabled = off; if (off) i.checked = false; });
      };

      if (none) {
        none.addEventListener('change', refresh);
        refresh();
      }
    });
  </script>
</x-app-layout>
