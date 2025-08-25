<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>{{ config('app.name', 'Reinscripciones') }}</title>

  {{-- Aplicar tema antes de cargar CSS para evitar parpadeo --}}
  <script>
  (function(){
    try {
      var e = document.documentElement;
      var stored = localStorage.getItem('theme');
      var prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
      var theme = (stored === 'dark' || stored === 'light') ? stored : (prefersDark ? 'dark' : 'light');
      if (theme === 'dark') e.classList.add('dark'); else e.classList.remove('dark');
    } catch(_) {}
  })();
  </script>

  @vite(['resources/css/app.css','resources/js/app.js'])
  @stack('head')
</head>
@php
  $user = auth()->user();
  $role = $user->role ?? null;
  $unread = 0;
  if ($user && class_exists(\App\Models\Notificacion::class)) {
    $unread = \App\Models\Notificacion::where('user_id', $user->id)->where('leida', false)->count();
  }
@endphp
<body class="h-full bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100" x-data="{ sidebarOpen: false }">

  {{-- TOASTS GLOBALES --}}
  @include('components.toast')

  {{-- NAVBAR --}}
  <header class="sticky top-0 z-40 bg-white/80 dark:bg-gray-900/80 backdrop-blur border-b border-gray-200 dark:border-gray-800">
    <div class="max-w-7xl mx-auto px-4 py-3 flex items-center gap-3">

      {{-- Toggle sidebar (móvil) --}}
      <button class="md:hidden btn-icon" @click="sidebarOpen = !sidebarOpen" aria-label="Abrir menú">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none"
             viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
          <path stroke-linecap="round" stroke-linejoin="round"
                d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/>
        </svg>
      </button>

      {{-- Brand --}}
      <a href="{{ route('home') }}" class="flex items-center gap-2">
        @if (file_exists(public_path('images/logo-uth.png')))
          <img src="{{ asset('images/logo-uth.png') }}" class="h-8 w-auto" alt="UTH">
        @endif
        <span class="font-semibold">Reinscripciones UTH</span>
      </a>

      {{-- Right zone --}}
      <div class="ml-auto flex items-center gap-2">
        {{-- Toggle de Tema (siempre visible) --}}
        <x-theme-toggle />

        @auth
          {{-- Badge de notificaciones solo para Estudiante --}}
          @if($role === 'ESTUDIANTE')
            <x-bell-badge :count="$unread" :href="route('est.notificaciones')" />
          @endif

          {{-- Dropdown de usuario --}}
          <x-dropdown align="right" width="48">
            <x-slot name="trigger">
              <button class="nav-link">
                <span class="h-8 w-8 rounded-full bg-uth-600 text-white flex items-center justify-center">
                  {{ strtoupper(mb_substr($user->name ?? 'U',0,1)) }}
                </span>
                <span class="hidden sm:inline">{{ $user->name }}</span>
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 opacity-70" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 011.08 1.04l-4.25 4.25a.75.75 0 01-1.08 0L5.21 8.27a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                </svg>
              </button>
            </x-slot>
            <x-slot name="content">
              @if($role === 'ESTUDIANTE')
                <x-dropdown-link :href="route('est.perfil.edit')">Mi perfil</x-dropdown-link>
                <x-dropdown-link :href="route('est.notificaciones')">
                  Notificaciones @if($unread>0)<span class="ml-2 chip chip-warn">{{ $unread }}</span>@endif
                </x-dropdown-link>
              @endif
              <x-dropdown-link :href="route('dashboard')">Ir al panel</x-dropdown-link>
              <form method="POST" action="{{ route('logout') }}" class="mt-1">
                @csrf
                <button class="w-full text-left px-3 py-2 text-sm rounded hover:bg-gray-100 dark:hover:bg-gray-700">
                  Cerrar sesión
                </button>
              </form>
            </x-slot>
          </x-dropdown>
        @else
          <a href="{{ route('login') }}" class="btn btn-primary">Ingresar</a>
        @endauth
      </div>
    </div>
  </header>

  {{-- Layout con sidebar --}}
  <div class="max-w-7xl mx-auto px-4">
    <div class="grid grid-cols-1 md:grid-cols-[240px_1fr] gap-6 py-6">

      {{-- SIDEBAR --}}
      <aside
        class="md:sticky md:top-[76px] md:h-[calc(100vh-90px)] md:overflow-auto bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-800 rounded-2xl p-4
               fixed top-[68px] left-0 right-0 mx-4 md:mx-0 z-30 md:z-auto shadow-soft md:shadow-none"
        :class="{'hidden md:block': !sidebarOpen, 'block': sidebarOpen}"
        @click.outside="sidebarOpen=false"
      >
        <div class="text-xs uppercase text-gray-500 mb-2">Menú</div>

        {{-- MENÚ POR ROL --}}
        @if($role === 'ESTUDIANTE')
          <a href="{{ route('dash.estudiante') }}" class="{{ request()->routeIs('dash.estudiante') ? 'aside-link-active' : 'aside-link' }}">Dashboard</a>
          <a href="{{ route('est.perfil.edit') }}" class="{{ request()->routeIs('est.perfil.*') ? 'aside-link-active' : 'aside-link' }}">Validación de datos</a>
          <a href="{{ route('est.pago.create') }}" class="{{ request()->routeIs('est.pago.*') ? 'aside-link-active' : 'aside-link' }}">Pago</a>
          <a href="{{ route('est.notificaciones') }}" class="{{ request()->routeIs('est.notificaciones') ? 'aside-link-active' : 'aside-link' }}">
            Notificaciones @if($unread>0)<span class="ml-2 chip chip-warn">{{ $unread }}</span>@endif
          </a>
        @elseif($role === 'CAJA')
          <a href="{{ route('dash.caja') }}" class="{{ request()->routeIs('dash.caja') ? 'aside-link-active' : 'aside-link' }}">Dashboard</a>
          <a href="{{ route('caja.pendientes') }}" class="{{ request()->routeIs('caja.pendientes') ? 'aside-link-active' : 'aside-link' }}">Pendientes</a>
          <a href="{{ route('caja.analisis') }}" class="{{ request()->routeIs('caja.analisis') ? 'aside-link-active' : 'aside-link' }}">Análisis de referencias</a>
        @elseif($role === 'ADMIN')
          <a href="{{ route('dash.admin') }}" class="{{ request()->routeIs('dash.admin') ? 'aside-link-active' : 'aside-link' }}">Dashboard</a>
          <a href="{{ route('admin.solicitudes') }}" class="{{ request()->routeIs('admin.solicitudes') ? 'aside-link-active' : 'aside-link' }}">Solicitudes</a>
        @else
          <div class="text-gray-500 text-sm">Inicia sesión para ver el menú.</div>
        @endif
      </aside>

      {{-- CONTENIDO --}}
      <main>
        @if (isset($header))
          <div class="mb-4">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $header }}</h1>
          </div>
        @endif

        {{ $slot }}
      </main>

    </div>
  </div>

  @stack('scripts')
</body>
</html>
