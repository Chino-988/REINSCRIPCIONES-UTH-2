@props(['count' => 0, 'href' => '#'])
<a href="{{ $href }}" class="relative btn-icon" aria-label="Notificaciones">
  {{-- Icono campana (Heroicon) --}}
  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5"
       fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
    <path stroke-linecap="round" stroke-linejoin="round"
          d="M14 10V6a4 4 0 10-8 0v4a2 2 0 01-.586 1.414L4 12v1h12v-1l-1.414-1.586A2 2 0 0114 10z" />
    <path stroke-linecap="round" stroke-linejoin="round" d="M10 21a2 2 0 002-2H8a2 2 0 002 2z"/>
  </svg>
  @if($count > 0)
    <span class="absolute -top-1 -right-1 h-5 min-w-[1.25rem] px-1 rounded-full bg-red-600 text-white text-xs flex items-center justify-center">
      {{ $count }}
    </span>
  @endif
</a>
