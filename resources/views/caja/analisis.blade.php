<x-app-layout>
<x-slot name="header">Caja — Análisis de referencias</x-slot>

<div class="space-y-6">
  @include('components.toast')

  @if(session('err'))
    <div class="p-3 bg-rose-600 text-white rounded">{{ session('err') }}</div>
  @endif
  @if(session('ok'))
    <div class="p-3 bg-emerald-600 text-white rounded">{{ session('ok') }}</div>
  @endif

  <div class="card">
    <div class="card-title">Subir archivo CSV</div>
    <form method="POST" action="{{ route('caja.analisis.procesar') }}" enctype="multipart/form-data" class="mt-4">
      @csrf
      <div class="grid sm:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium mb-1">Archivo (.csv)</label>
          <input type="file" name="archivo" accept=".csv,text/csv" class="w-full border rounded p-2 bg-white dark:bg-gray-900">
          @error('archivo') <div class="text-rose-600 text-sm mt-1">{{ $message }}</div> @enderror
          <p class="text-xs text-gray-500 mt-1">Si tu archivo es Excel, ábrelo y <b>Guárdalo como CSV</b> antes de subirlo.</p>
        </div>
      </div>
      <button class="btn btn-primary mt-4">Procesar</button>
    </form>
  </div>

  @if(!empty($preview) && is_array($preview))
    <div class="card">
      <div class="card-title">Resultado — vista previa ({{ $total }} filas)</div>
      @php
        $hdrs = $preview['headers'] ?? [];
        $rows = $preview['rows'] ?? [];
      @endphp
      <div class="overflow-x-auto mt-3">
        <table class="min-w-full text-sm">
          <thead>
            <tr>
              @foreach($hdrs as $h)
                <th class="px-3 py-2 text-left font-semibold border-b">{{ $h }}</th>
              @endforeach
            </tr>
          </thead>
          <tbody>
            @forelse($rows as $r)
              <tr class="border-b">
                @foreach($r as $c)
                  <td class="px-3 py-2">{{ $c }}</td>
                @endforeach
              </tr>
            @empty
              <tr><td class="px-3 py-2 text-gray-500">Sin filas.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
      <div class="mt-3">
        <a href="{{ route('caja.analisis.descargar') }}" class="btn btn-secondary">Descargar CSV analizado</a>
      </div>
    </div>
  @endif
</div>
</x-app-layout>
