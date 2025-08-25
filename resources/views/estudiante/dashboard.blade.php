<x-app-layout>
<x-slot name="header">Panel del Estudiante</x-slot>

<div class="space-y-6">
  @include('components.toast')

  {{-- Mensajes rápidos --}}
  @if (session('ok'))
    <div class="p-3 bg-emerald-600 text-white rounded">{{ session('ok') }}</div>
  @endif
  @if (session('err'))
    <div class="p-3 bg-rose-600 text-white rounded">{{ session('err') }}</div>
  @endif
  @if (session('info'))
    <div class="p-3 bg-blue-600 text-white rounded">{{ session('info') }}</div>
  @endif

  {{-- ===== NUEVO: Estado de Reinscripción (desde API interna) ===== --}}
  <div class="card" id="estado-reinscripcion">
    <div class="flex items-center justify-between">
      <div class="card-title">Estado de mi reinscripción</div>
      <button id="btn-refrescar-estado" type="button" class="btn btn-secondary">Actualizar</button>
    </div>

    <div class="mt-3 inline-block rounded-full px-3 py-1 text-sm font-semibold" id="estado-badge"
         style="background:#e5e7eb;color:#111827;">Cargando...</div>

    <div id="estado-mensaje" class="mt-2 text-gray-800">—</div>

    <div id="estado-alertas" class="mt-3 space-y-1"></div>

    <div id="estado-extra" class="mt-3 text-sm text-gray-500"></div>
  </div>

  @php
    $estu  = $estu  ?? null;
    $pago  = $pago  ?? null;
    $rein  = $rein  ?? null;
    $notis = $notis ?? collect();

    // ¿Perfil enviado?
    $perfilEnviadoCalc = $estu ? (
      (bool)($estu->validado_en ?? false)
      || (bool) data_get($estu, 'validado_datos', false)
      || (bool) session('perfil_enviado', false)
    ) : false;

    // ¿Rechazo de admin?
    $rechazoAdminCalc = ($rein && ($rein->estatus_final ?? null) === 'RECHAZADA');

    // Bloqueo perfil
    if (!isset($bloqPerfil)) {
      $bloqPerfil = $perfilEnviadoCalc && (!$rechazoAdminCalc);
    }

    // Permitir pago sólo si perfil fue enviado y no hay pago activo
    if (!isset($permitePago)) {
      $permitePago = $perfilEnviadoCalc && ( !$pago || (($pago->estatus_caja ?? null) === 'RECHAZADO') );
    }
    if (!isset($bloqPagoBtn)) {
      $bloqPagoBtn = ! $permitePago;
    }

    // Para la barra de progreso
    $tienePago = (bool)($pago ?? false);
    $step1   = $perfilEnviadoCalc;
    $step2   = $tienePago;
    $step3   = $tienePago && (($pago->estatus_caja ?? null) === 'VALIDADO');
    $rechCaja= $tienePago && (($pago->estatus_caja ?? null) === 'RECHAZADO');
    $step4   = (($rein->estatus_final ?? null) === 'APROBADA');
  @endphp

  {{-- Barra de progreso (4 pasos) --}}
  <div class="card">
    <div class="card-title">Progreso de reinscripción</div>
    <ol class="mt-4 grid grid-cols-1 md:grid-cols-4 gap-3">
      <li class="p-3 rounded-lg border {{ $step1?'border-emerald-500 bg-emerald-50 dark:bg-emerald-900/20':'border-gray-300' }}">
        <div class="font-semibold">1) Validación de datos</div>
        <div class="text-sm mt-1">{{ $step1 ? 'Completada' : 'Pendiente' }}</div>
      </li>
      <li class="p-3 rounded-lg border {{ $step2?'border-blue-500 bg-blue-50 dark:bg-blue-900/20':'border-gray-300' }}">
        <div class="font-semibold">2) Envío de pago</div>
        <div class="text-sm mt-1">
          @if(!$tienePago)
            Pendiente
          @else
            Enviado ({{ optional($pago->created_at)->format('d/m H:i') }})
          @endif
        </div>
      </li>
      <li class="p-3 rounded-lg border {{ $step3?'border-emerald-500 bg-emerald-50 dark:bg-emerald-900/20':($rechCaja?'border-rose-500 bg-rose-50 dark:bg-rose-900/20':'border-gray-300') }}">
        <div class="font-semibold">3) Validación de Caja</div>
        <div class="text-sm mt-1">
          @if($step3)
            Validado
          @elseif($rechCaja)
            Rechazado ({{ $pago->observaciones_caja ?? 'Sin motivo' }})
          @else
            En revisión
          @endif
        </div>
      </li>
      <li class="p-3 rounded-lg border {{ $step4?'border-emerald-500 bg-emerald-50 dark:bg-emerald-900/20':'border-gray-300' }}">
        <div class="font-semibold">4) Aprobación y constancia</div>
        <div class="text-sm mt-1">
          @if($step4)
            Aprobada
          @elseif(($rein->estatus_final ?? null) === 'RECHAZADA')
            Rechazada
          @else
            Pendiente
          @endif
        </div>
      </li>
    </ol>
  </div>

  {{-- Tarjetas de acciones (bloqueo por estado) --}}
  <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
    {{-- Validación de datos --}}
    <div class="card">
      <div class="card-title">Validación de datos</div>
      <p class="text-sm mt-1">Completa tus datos personales y socioeconómicos.</p>
      <div class="mt-3">
        @if($bloqPerfil)
          <button class="btn btn-secondary opacity-60 cursor-not-allowed" disabled>En revisión</button>
          <p class="text-xs text-gray-500 mt-1">Si el Admin rechaza, podrás editarlos.</p>
        @else
          <a href="{{ route('est.perfil.edit') }}" class="btn btn-primary">Editar / Validar</a>
        @endif
      </div>
    </div>

    {{-- Registro de pago --}}
    <div class="card">
      <div class="card-title">Registro de pago</div>
      <p class="text-sm mt-1">Sube tu referencia y comprobante.</p>
      <div class="mt-3">
        @if($bloqPagoBtn)
          <button class="btn btn-secondary opacity-60 cursor-not-allowed" disabled>
            @if(!$perfilEnviadoCalc)
              Primero valida tus datos
            @elseif($pago && ($pago->estatus_caja ?? null) !== 'RECHAZADO')
              {{ ($pago && ($pago->estatus_caja ?? null)==='VALIDADO') ? 'Validado por Caja' : 'En revisión' }}
            @else
              No disponible
            @endif
          </button>
          @if($rechCaja)
            <p class="text-xs text-rose-600 mt-1">Rechazado: {{ $pago->observaciones_caja }}</p>
          @endif
        @else
          <a href="{{ route('est.pago.create') }}" class="btn btn-primary">Cargar pago</a>
        @endif
      </div>
    </div>

    {{-- Constancia --}}
    <div class="card">
      <div class="card-title">Constancia</div>
      <p class="text-sm mt-1">Descarga tu constancia cuando sea aprobada.</p>
      @if((($rein->estatus_final ?? null) === 'APROBADA') && !empty($rein->constancia_pdf_path))
        <div class="mt-3 flex flex-wrap gap-2">
          <a href="{{ route('est.constancia.descargar') }}" class="btn btn-primary">Descargar constancia</a>
          @if(!empty($rein->token_verificacion))
            <a target="_blank" href="{{ route('verificacion.mostrar',$rein->token_verificacion) }}" class="btn btn-secondary">Verificación</a>
          @endif
        </div>
      @else
        <div class="mt-3">
          <button class="btn btn-secondary opacity-60 cursor-not-allowed" disabled>No disponible</button>
        </div>
      @endif
    </div>
  </div>

  {{-- Notificaciones --}}
  <div class="card">
    <div class="card-title">Notificaciones</div>
    <ul class="mt-2 list-disc ml-6">
      @forelse($notis as $n)
        <li class="text-sm">
          <b>{{ $n->titulo }}</b> — {{ $n->mensaje }}
          <span class="text-xs text-gray-500">({{ optional($n->created_at)->format('d/m H:i') }})</span>
        </li>
      @empty
        <li class="text-sm text-gray-500">Sin notificaciones.</li>
      @endforelse
    </ul>
  </div>
</div>

{{-- JS del card de estado --}}
<script>
document.addEventListener('DOMContentLoaded', () => {
  const url = @json(route('estudiante.estado'));

  const $badge   = document.getElementById('estado-badge');
  const $msg     = document.getElementById('estado-mensaje');
  const $alertas = document.getElementById('estado-alertas');
  const $extra   = document.getElementById('estado-extra');
  const $btn     = document.getElementById('btn-refrescar-estado');

  const pintaBadge = (estado) => {
    let bg = '#e5e7eb', fg = '#111827';
    if (estado === 'PROCESO NO INICIADO')          { bg = '#f3f4f6'; fg = '#374151'; } // gris
    if (estado === 'EN PROCESO DE REINSCRIPCIÓN')  { bg = '#fff7ed'; fg = '#9a3412'; } // ámbar
    if (estado === 'REINSCRITO')                   { bg = '#ecfdf5'; fg = '#065f46'; } // verde
    if (estado === 'RECHAZADO')                    { bg = '#fef2f2'; fg = '#991b1b'; } // rojo
    $badge.style.background = bg;
    $badge.style.color      = fg;
    $badge.textContent      = estado;
  };

  const pintaAlertas = (arr) => {
    $alertas.innerHTML = '';
    if (!arr || !arr.length) return;
    arr.forEach(a => {
      const d = document.createElement('div');
      d.className = 'p-2 rounded border';
      d.style.background = '#fff1f2';
      d.style.borderColor = '#fecdd3';
      d.style.color = '#9f1239';
      d.textContent = '⚠ ' + a;
      $alertas.appendChild(d);
    });
  };

  const pintaExtra = (datos) => {
    if (!datos) { $extra.textContent = ''; return; }
    let lineas = [];
    if (datos.validacion_datos_enviada === false) {
      lineas.push('Aún no has enviado tu Validación de datos.');
    }
    if (datos.pago) {
      const ec = datos.pago.estatus_caja ?? '—';
      const ea = datos.pago.estatus_admin ?? '—';
      lineas.push(`Referencia: ${datos.pago.referencia} · Caja: ${ec} · Admin: ${ea}`);
    }
    if (datos.constancia_pdf) {
      lineas.push('Tu constancia está disponible para descarga.');
    }
    $extra.textContent = lineas.join('  |  ');
  };

  const cargar = async () => {
    try {
      $badge.textContent = 'Consultando...';
      const res  = await fetch(url, { headers: { 'Accept': 'application/json' } });
      const json = await res.json();
      if (!json.ok) throw new Error(json.message || 'Error consultando estado');

      pintaBadge(json.estado);
      $msg.textContent = json.mensaje || '';
      pintaAlertas(json.alertas || []);
      pintaExtra(json.datos || {});
    } catch (e) {
      // Fallback conservador ahora es PROCESO NO INICIADO
      pintaBadge('PROCESO NO INICIADO');
      $msg.textContent = 'No se pudo obtener el estado. Intenta nuevamente.';
      pintaAlertas([]);
      $extra.textContent = '';
      console.error(e);
    }
  };

  $btn.addEventListener('click', cargar);
  cargar(); // primera carga
});
</script>
</x-app-layout>
