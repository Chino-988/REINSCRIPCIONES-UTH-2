@php
  $ok  = session('ok');
  $err = session('err');
@endphp

@if ($ok || $err || ($errors->any() ?? false))
  <div
    x-data="{ show: true }"
    x-show="show"
    x-init="setTimeout(() => show = false, 4200)"
    class="fixed top-5 right-5 z-50 space-y-2 w-[calc(100%-2.5rem)] sm:w-96"
  >
    @if($ok)
      <div class="p-3 rounded-xl shadow-soft bg-uth-600 text-white">
        <div class="font-semibold text-sm">Éxito</div>
        <div class="text-sm">{{ $ok }}</div>
      </div>
    @endif

    @if($err)
      <div class="p-3 rounded-xl shadow-soft bg-red-600 text-white">
        <div class="font-semibold text-sm">Error</div>
        <div class="text-sm">{{ $err }}</div>
      </div>
    @endif

    @if ($errors->any())
      <div class="p-3 rounded-xl shadow-soft bg-red-700 text-white">
        <div class="font-semibold text-sm mb-1">Revisa los campos</div>
        <ul class="list-disc ml-5 text-sm">
          @foreach ($errors->all() as $e)
            <li>{{ $e }}</li>
          @endforeach
        </ul>
      </div>
    @endif
  </div>
@endif
