<button
  x-data="{ isDark: document.documentElement.classList.contains('dark') }"
  @click="isDark = !isDark; window.setTheme(isDark ? 'dark' : 'light')"
  class="btn-icon"
  :title="isDark ? 'Cambiar a modo claro' : 'Cambiar a modo oscuro'"
  aria-label="Cambiar tema"
  x-cloak
>
  {{-- Sol (modo claro) --}}
  <svg x-show="!isDark" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
    <path d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zM4.22 4.22a1 1 0 011.42 0l.7.7a1 1 0 11-1.42 1.42l-.7-.7a1 1 0 010-1.42zM2 11a1 1 0 100-2h-1a1 1 0 100 2h1zm3.34 5.05a1 1 0 00-1.41 1.41l.7.7a1 1 0 001.42-1.41l-.71-.7zM10 17a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zm6.66-1.95a1 1 0 10-1.41 1.41l.7.7a1 1 0 001.42-1.41l-.71-.7zM19 9h-1a1 1 0 100 2h1a1 1 0 100-2zm-4.34-3.95a1 1 0 011.41-1.41l.7.7a1 1 0 01-1.41 1.41l-.7-.7zM10 5a5 5 0 100 10A5 5 0 0010 5z"/>
  </svg>
  {{-- Luna (modo oscuro) --}}
  <svg x-show="isDark" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
    <path d="M17.293 13.293A8 8 0 116.707 2.707 7 7 0 1017.293 13.293z"/>
  </svg>
</button>
