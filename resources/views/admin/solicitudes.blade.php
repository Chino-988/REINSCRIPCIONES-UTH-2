<x-app-layout>
<x-slot name="header">Solicitudes de Reinscripción</x-slot>

<div class="space-y-6">
  @include('components.toast')

  {{-- FILTROS --}}
  @php $q = $q ?? request('q'); $estado = $estado ?? request('estado','TODOS'); @endphp
  <form method="GET" class="card flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-3 w-full">
      <div>
        <label class="label">Búsqueda</label>
        <input type="text" name="q" value="{{ $q }}" class="input" placeholder="Matricula / nombre / referencia">
      </div>
      <div>
        <label class="label">Estado</label>
        <select name="estado" class="select">
          @php
            $opts = [
              'TODOS' => 'Todos',
              'CAJA_PEND' => 'Caja: Pendientes',
              'CAJA_OK'   => 'Caja: Validados',
              'CAJA_RECH' => 'Caja: Rechazados',
              'ADMIN_PEND'=> 'Admin: Pendientes',
              'ADMIN_OK'  => 'Admin: Validados',
              'ADMIN_RECH'=> 'Admin: Rechazados',
            ];
          @endphp
          @foreach($opts as $k=>$v)
            <option value="{{ $k }}" {{ $estado===$k?'selected':'' }}>{{ $v }}</option>
          @endforeach
        </select>
      </div>
    </div>
    <div class="flex gap-2">
      <button class="btn btn-primary">Filtrar</button>
      <a href="{{ route('admin.solicitudes') }}" class="btn btn-secondary">Limpiar</a>
    </div>
  </form>

  {{-- TABLA --}}
  <div class="card">
    <div class="overflow-auto">
      <table class="table">
        <thead>
          <tr>
            <th class="th">#</th>
            <th class="th">Fecha</th>
            <th class="th">Matrícula</th>
            <th class="th">Alumno</th>
            <th class="th">Referencia</th>
            <th class="th">Caja</th>
            <th class="th">Admin</th>
            <th class="th">Acciones</th>
          </tr>
        </thead>
        <tbody>
          @forelse($pagos as $p)
            <tr>
              <td class="td">{{ $p->id }}</td>
              <td class="td">{{ optional($p->created_at)->format('d/m/Y H:i') }}</td>
              <td class="td">{{ $p->estudiante->matricula ?? '' }}</td>
              <td class="td">{{ trim(($p->estudiante->nombre ?? '').' '.($p->estudiante->apellido_paterno ?? '')) }}</td>
              <td class="td">{{ $p->referencia }}</td>
              <td class="td">
                @if($p->estatus_caja === 'VALIDADO')
                  <span class="px-2 py-1 rounded bg-emerald-600 text-white text-xs">VALIDADO</span>
                @elseif($p->estatus_caja === 'RECHAZADO')
                  <span class="px-2 py-1 rounded bg-rose-600 text-white text-xs">RECHAZADO</span>
                @else
                  <span class="px-2 py-1 rounded bg-yellow-500 text-white text-xs">PENDIENTE</span>
                @endif
              </td>
              <td class="td">
                @if($p->estatus_admin === 'VALIDADO')
                  <span class="px-2 py-1 rounded bg-emerald-600 text-white text-xs">VALIDADO</span>
                @elseif($p->estatus_admin === 'RECHAZADO')
                  <span class="px-2 py-1 rounded bg-rose-600 text-white text-xs">RECHAZADO</span>
                @else
                  <span class="px-2 py-1 rounded bg-yellow-500 text-white text-xs">PENDIENTE</span>
                @endif
              </td>
              <td class="td">
                <a class="btn btn-secondary" href="{{ route('admin.ver',$p) }}">Revisar</a>
              </td>
            </tr>
          @empty
            <tr><td class="td text-center" colspan="8">Sin resultados</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <div class="mt-4">
      {{ $pagos->links() }}
    </div>
  </div>
</div>
</x-app-layout>
