<x-app-layout>
<x-slot name="header">Panel — Administrador</x-slot>

<div class="space-y-6">
  @include('components.toast')

  <!-- Tarjetas -->
  <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4">
    <div class="card">
      <div class="card-title">Pendientes de Caja</div>
      <div class="mt-2 text-3xl font-bold">{{ $pendCaja }}</div>
    </div>
    <div class="card">
      <div class="card-title">Pendientes de Admin</div>
      <div class="mt-2 text-3xl font-bold">{{ $pendAdmin }}</div>
    </div>
    <div class="card">
      <div class="card-title">Aprobadas</div>
      <div class="mt-2 text-3xl font-bold text-emerald-600">{{ $aprobadas }}</div>
    </div>
    <div class="card">
      <div class="card-title">Rechazadas</div>
      <div class="mt-2 text-3xl font-bold text-rose-600">{{ $rechazadas }}</div>
    </div>
  </div>

  <div class="flex gap-3">
    <a href="{{ route('admin.solicitudes') }}" class="btn btn-primary">Ver solicitudes</a>
    <a href="{{ route('admin.solicitudes.exportar') }}" class="btn btn-secondary">Exportar CSV</a>
  </div>

  <!-- Últimos pagos -->
  <div class="card">
    <div class="card-title">Últimos movimientos</div>
    <div class="overflow-x-auto mt-3">
      <table class="min-w-full text-sm">
        <thead>
          <tr class="text-left border-b">
            <th class="px-3 py-2">Fecha</th>
            <th class="px-3 py-2">Matrícula</th>
            <th class="px-3 py-2">Alumno</th>
            <th class="px-3 py-2">Referencia</th>
            <th class="px-3 py-2">Caja</th>
            <th class="px-3 py-2">Admin</th>
            <th class="px-3 py-2">Acción</th>
          </tr>
        </thead>
        <tbody>
          @forelse($ultimos as $p)
            <tr class="border-b">
              <td class="px-3 py-2">{{ $p->created_at->format('d/m/Y H:i') }}</td>
              <td class="px-3 py-2">{{ optional($p->estudiante)->matricula }}</td>
              <td class="px-3 py-2">
                {{ optional($p->estudiante)->nombre }}
                {{ optional($p->estudiante)->apellido_paterno }}
                {{ optional($p->estudiante)->apellido_materno }}
              </td>
              <td class="px-3 py-2">{{ $p->referencia }}</td>
              <td class="px-3 py-2">
                @if($p->estatus_caja==='VALIDADO') <span class="text-emerald-600 font-semibold">VALIDADO</span>
                @elseif($p->estatus_caja==='RECHAZADO') <span class="text-rose-600 font-semibold">RECHAZADO</span>
                @else <span class="text-amber-600 font-semibold">PENDIENTE</span>
                @endif
              </td>
              <td class="px-3 py-2">
                @if($p->estatus_admin==='VALIDADO') <span class="text-emerald-600 font-semibold">VALIDADO</span>
                @elseif($p->estatus_admin==='RECHAZADO') <span class="text-rose-600 font-semibold">RECHAZADO</span>
                @else <span class="text-amber-600 font-semibold">PENDIENTE</span>
                @endif
              </td>
              <td class="px-3 py-2">
                <a href="{{ route('admin.ver',$p) }}" class="text-indigo-600 underline">Revisar</a>
              </td>
            </tr>
          @empty
            <tr><td class="px-3 py-3 text-gray-500" colspan="7">Sin registros.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>
</x-app-layout>
