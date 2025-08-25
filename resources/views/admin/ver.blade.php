<x-app-layout>
@include('components.toast')

<x-slot name="header">
  <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
    Revisar solicitud — Pago #{{ $pago->id }}
  </h2>
</x-slot>

<div class="py-6">
<div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

  {{-- ALERTAS --}}
  @if (session('ok'))
    <div class="p-3 bg-green-600 text-white rounded">{{ session('ok') }}</div>
  @endif
  @if (session('err'))
    <div class="p-3 bg-red-600 text-white rounded">{{ session('err') }}</div>
  @endif

  {{-- ALUMNO --}}
  <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-6">
    <h3 class="font-semibold mb-4">Datos del alumno</h3>
    <div class="grid sm:grid-cols-2 gap-4 text-sm">
      <div><b>Matrícula:</b> {{ $pago->estudiante->matricula }}</div>
      <div><b>Nombre:</b> {{ $pago->estudiante->nombre }} {{ $pago->estudiante->apellido_paterno }} {{ $pago->estudiante->apellido_materno }}</div>
      <div><b>Etnia/pueblo:</b> {{ $pago->estudiante->pertenencia_etnica ?? 'N/D' }}</div>
      <div><b>Lengua:</b> {{ $pago->estudiante->lengua_indigena ?? 'N/D' }}</div>
      <div><b>Ingreso mensual:</b> {{ $pago->estudiante->ingreso_mensual ?? 'N/D' }}</div>
      <div><b>Dependientes:</b> {{ $pago->estudiante->dependientes ?? 'N/D' }}</div>
      <div><b>Estado civil:</b> {{ $pago->estudiante->estado_civil ?? 'N/D' }}</div>
      <div><b>Validado en:</b> {{ optional($pago->estudiante->validado_en)->format('d/m/Y H:i') ?? 'No' }}</div>
    </div>

    @php
      $tels = $pago->estudiante->telefonos ?? [];
      $cors = $pago->estudiante->correos ?? [];
      $doms = $pago->estudiante->domicilios ?? [];

      $isList = function($arr) { return is_array($arr) && array_keys($arr) === range(0, count($arr) - 1); };
    @endphp

    <div class="grid sm:grid-cols-3 gap-6 mt-4">
      <div>
        <h4 class="font-semibold mb-2">Teléfonos</h4>
        <ul class="list-disc ml-5">
          @if($isList($tels))
            @forelse($tels as $t)
              <li>{{ $t['tipo'] ?? 'Tel' }}: {{ $t['dato'] ?? '' }} <span class="text-xs text-gray-500">({{ $t['comentario'] ?? '' }})</span></li>
            @empty
              <li class="text-gray-500">N/D</li>
            @endforelse
          @else
            @php
              $map = [
                'movil' => 'Móvil',
                'padre' => 'Padre',
                'madre' => 'Madre',
              ];
            @endphp
            @forelse($map as $k=>$label)
              @if(!empty($tels[$k]))
                <li>{{ $label }}: {{ $tels[$k] }}</li>
              @endif
            @empty
              <li class="text-gray-500">N/D</li>
            @endforelse
          @endif
        </ul>
      </div>

      <div>
        <h4 class="font-semibold mb-2">Correos</h4>
        <ul class="list-disc ml-5">
          @if($isList($cors))
            @forelse($cors as $c)
              <li>{{ $c['tipo'] ?? 'Correo' }}: {{ $c['dato'] ?? '' }} <span class="text-xs text-gray-500">({{ $c['comentario'] ?? '' }})</span></li>
            @empty
              <li class="text-gray-500">N/D</li>
            @endforelse
          @else
            @php $map = ['institucional'=>'Institucional','personal'=>'Personal']; @endphp
            @forelse($map as $k=>$label)
              @if(!empty($cors[$k]))
                <li>{{ $label }}: {{ $cors[$k] }}</li>
              @endif
            @empty
              <li class="text-gray-500">N/D</li>
            @endforelse
          @endif
        </ul>
      </div>

      <div>
        <h4 class="font-semibold mb-2">Domicilios</h4>
        <ul class="list-disc ml-5">
          @if($isList($doms))
            @forelse($doms as $d)
              <li>{{ $d['direccion'] ?? '' }} <span class="text-xs text-gray-500">({{ $d['comentario'] ?? '' }})</span></li>
            @empty
              <li class="text-gray-500">N/D</li>
            @endforelse
          @else
            @if(!empty($doms['principal']))
              <li>{{ $doms['principal'] }}</li>
            @else
              <li class="text-gray-500">N/D</li>
            @endif
          @endif
        </ul>
      </div>
    </div>

    <div class="mt-4 text-sm">
      <b>Tutor:</b>
      {{ $pago->estudiante->tutor_trato }} {{ $pago->estudiante->tutor_nombre }}
      {{ $pago->estudiante->tutor_apellido_paterno }} {{ $pago->estudiante->tutor_apellido_materno }}
    </div>
  </div>

  {{-- CONDICIONES FUNCIONALES --}}
  <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-6">
    <h3 class="font-semibold mb-4">Condiciones funcionales</h3>
    @php $c = $pago->estudiante->condicion; @endphp
    @if(!$c)
      <p class="text-gray-500">Sin registro.</p>
    @else
      @php
        $labels = [
          'ninguna'=>'Ninguna',
          'estar_de_pie_mareo'=>'Mareo al estar de pie',
          'caminar_sin_ayuda'=>'No puede caminar sin ayuda',
          'desplazar_problemas'=>'Problemas para desplazarse',
          'manipular_no_dibuja_casa'=>'Dificultad al manipular objetos',
          'hablar_no_solicita_ayuda'=>'No puede solicitar ayuda en emergencia',
          'postura_pierde_fuerza'=>'Pérdida de fuerza en postura',
          'otras_acciones_no_deporte'=>'No puede realizar deportes',
          'oido_izq_oigo_poco'=>'Oído izq: oigo poco',
          'oido_der_oigo_poco'=>'Oído der: oigo poco',
          'oido_izq_no_oigo'=>'Oído izq: no oigo',
          'oido_der_no_oigo'=>'Oído der: no oigo',
          'ojo_izq_casi_no_ve'=>'Ojo izq: casi no ve',
          'ojo_der_casi_no_ve'=>'Ojo der: casi no ve',
          'ojo_izq_no_ve'=>'Ojo izq: no ve',
          'ojo_der_no_ve'=>'Ojo der: no ve',
          'tarda_comprender_lectura'=>'Tarda en comprender lectura',
          'no_entiende_lectura'=>'No entiende lectura',
          'escritura_no_entendible'=>'Escritura no entendible',
          'dificultad_lect_escr_mapa'=>'Dificultad lect./escr./mapa',
          'dificultad_matematicas_basicas'=>'Dificultad matemáticas básicas',
          'olvida_datos_personales'=>'Olvida datos personales',
          'dificultad_interactuar'=>'Dificultad al interactuar',
          'dificultad_establecer_platica'=>'Dificultad para platicar',
          'prefiere_solo'=>'Prefiere estar solo',
          'prefiere_trabajar_solo'=>'Prefiere trabajar solo',
          'escucha_voces'=>'Escucha voces',
          've_personas_objetos'=>'Ve personas/objetos',
          'cambios_estado_animo'=>'Cambios de ánimo',
          'enfermedad_nacimiento'=>'Enfermedad de nacimiento',
          'enfermedad_cronica'=>'Enfermedad crónica',
        ];
      @endphp
      <div class="flex flex-wrap gap-2">
        @foreach($labels as $key => $txt)
          @if($c->$key)
            <span class="px-2 py-1 bg-rose-600 text-white rounded text-xs">{{ $txt }}</span>
          @endif
        @endforeach
        @if(!$c->getAttributes())<span class="text-gray-500">Ninguna</span>@endif
      </div>
    @endif
  </div>

  {{-- PAGO --}}
  <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-6">
    <h3 class="font-semibold mb-4">Pago</h3>
    <div class="grid sm:grid-cols-2 gap-4 text-sm">
      <div><b>Referencia:</b> {{ $pago->referencia }}</div>
      <div>
        <b>Comprobante:</b>
        @if($pago->comprobante_path)
          <a class="text-indigo-600 underline" target="_blank"
             href="{{ asset('storage/'.$pago->comprobante_path) }}">ver archivo</a>
        @else
          N/D
        @endif
      </div>
      <div>
        <b>Estatus Caja:</b>
        @if($pago->estatus_caja === 'VALIDADO')
          <span class="px-2 py-1 bg-green-600 text-white rounded text-xs">VALIDADO</span>
        @elseif($pago->estatus_caja === 'RECHAZADO')
          <span class="px-2 py-1 bg-red-600 text-white rounded text-xs">RECHAZADO</span>
        @else
          <span class="px-2 py-1 bg-yellow-500 text-white rounded text-xs">PENDIENTE</span>
        @endif
      </div>
      <div>
        <b>Estatus Admin:</b>
        @if($pago->estatus_admin === 'VALIDADO')
          <span class="px-2 py-1 bg-green-600 text-white rounded text-xs">VALIDADO</span>
        @elseif($pago->estatus_admin === 'RECHAZADO')
          <span class="px-2 py-1 bg-red-600 text-white rounded text-xs">RECHAZADO</span>
        @else
          <span class="px-2 py-1 bg-yellow-500 text-white rounded text-xs">PENDIENTE</span>
        @endif
      </div>
    </div>
  </div>

  {{-- ACCIONES --}}
  <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-6">
    <h3 class="font-semibold mb-2">Acciones</h3>

    @if($pago->estatus_caja !== 'VALIDADO')
      <div class="p-3 bg-yellow-100 text-yellow-900 rounded mb-3">
        En espera de validación por Caja. Cuando Caja valide, podrás emitir la constancia.
      </div>
    @endif

    <div class="grid sm:grid-cols-2 gap-4">
      {{-- Finalizar (emitir constancia) --}}
      <form method="POST" action="{{ route('admin.finalizar', $pago) }}">
        @csrf
        <label class="block text-sm mb-1">Observaciones (opcional)</label>
        <textarea
          name="observaciones"
          class="w-full border rounded p-2 text-gray-900 dark:text-gray-100 bg-white dark:bg-gray-900"
          rows="3"
          placeholder="Notas internas...">{{ old('observaciones') }}</textarea>
        <button class="mt-2 px-4 py-2 bg-emerald-600 text-white rounded"
                {{ $pago->estatus_caja !== 'VALIDADO' ? 'disabled' : '' }}>
          Aprobar y emitir constancia
        </button>
        @if($pago->estatus_caja !== 'VALIDADO')
          <div class="text-xs text-gray-500 mt-1">Primero debe validar Caja.</div>
        @endif
      </form>

      {{-- Rechazar --}}
      <form method="POST" action="{{ route('admin.rechazar', $pago) }}"
            onsubmit="return confirm('¿Seguro que deseas rechazar esta solicitud?');">
        @csrf
        <label class="block text-sm mb-1">Motivo de rechazo</label>
        <textarea
          name="motivo"
          class="w-full border rounded p-2 text-gray-900 dark:text-gray-100 bg-white dark:bg-gray-900"
          rows="3"
          placeholder="Escribe el motivo..." required>{{ old('motivo') }}</textarea>
        <button class="mt-2 px-4 py-2 bg-red-600 text-white rounded">Rechazar</button>
      </form>
    </div>

    {{-- Si ya hay constancia, mostrar descarga + verificación --}}
    @php $rein = $pago->estudiante->reinscripcion; @endphp
    @if($rein && $rein->estatus_final === 'APROBADA')
      <hr class="my-4">
      <div class="flex items-center gap-3 text-sm">
        <div>
          <b>Constancia:</b>
          @if($rein->constancia_pdf_path)
            <a class="text-indigo-600 underline" target="_blank"
               href="{{ asset('storage/'.$rein->constancia_pdf_path) }}">descargar PDF</a>
          @else
            N/D
          @endif
        </div>
        <div>
          <b>Verificación pública:</b>
          @if($rein->token_verificacion)
            <a class="text-indigo-600 underline" target="_blank"
               href="{{ route('verificacion.mostrar', $rein->token_verificacion) }}">
               {{ route('verificacion.mostrar', $rein->token_verificacion) }}
            </a>
          @else
            N/D
          @endif
        </div>
      </div>
    @endif

  </div>

</div>
</div>
</x-app-layout>
