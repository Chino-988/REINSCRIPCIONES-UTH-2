<x-app-layout>
@include('components.toast')
<x-slot name="header">
  <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
    Mis notificaciones
  </h2>
</x-slot>

<div class="py-6">
<div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">

  <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-6">
    @if (session('ok'))
      <div class="mb-3 p-3 bg-green-600 text-white rounded">{{ session('ok') }}</div>
    @endif
    @if (session('err'))
      <div class="mb-3 p-3 bg-red-600 text-white rounded">{{ session('err') }}</div>
    @endif

    @if($notis->isEmpty())
      <p class="text-gray-500 text-sm">No hay notificaciones.</p>
    @else
      <ul class="space-y-3">
        @foreach($notis as $n)
          <li class="p-3 rounded border {{ $n->leida ? 'opacity-70' : '' }}">
            <div class="flex items-center justify-between">
              <div class="text-sm font-semibold">{{ $n->titulo }}</div>
              <div class="text-xs text-gray-500">{{ $n->created_at->format('d/m/Y H:i') }}</div>
            </div>
            <div class="text-sm mt-1">{{ $n->mensaje }}</div>

            @if(!$n->leida)
              <form method="POST" action="{{ route('est.noti.leer', $n->id) }}" class="mt-2">
                @csrf
                <button class="px-3 py-1 text-xs bg-indigo-600 text-white rounded">Marcar como leída</button>
              </form>
            @endif
          </li>
        @endforeach
      </ul>

      <div class="mt-4">
        {{ $notis->links() }}
      </div>
    @endif
  </div>

</div>
</div>
</x-app-layout>
