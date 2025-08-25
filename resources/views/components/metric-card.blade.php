@props([
  'title' => 'Métrica',
  'value' => '0',
  'hint'  => null,
  'color' => 'uth',  // uth | green | yellow | red | gray
  'icon'  => null,   // svg string opcional
])

@php
$colors = [
  'uth'   => 'bg-uth-600/10 text-uth-700 dark:text-uth-300',
  'green' => 'bg-green-600/10 text-green-700 dark:text-green-300',
  'yellow'=> 'bg-yellow-500/10 text-yellow-700 dark:text-yellow-300',
  'red'   => 'bg-red-600/10 text-red-700 dark:text-red-300',
  'gray'  => 'bg-gray-600/10 text-gray-700 dark:text-gray-300',
];
$chip = $colors[$color] ?? $colors['gray'];
@endphp

<div class="card p-4 flex items-center gap-4">
  <div class="h-12 w-12 rounded-xl flex items-center justify-center {{ $chip }}">
    @if ($icon)
      {!! $icon !!}
    @else
      <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 opacity-80" viewBox="0 0 20 20" fill="currentColor">
        <path d="M2 11a1 1 0 011-1h10a1 1 0 110 2H3a1 1 0 01-1-1z"/>
        <path d="M5 7a1 1 0 011-1h8a1 1 0 110 2H6a1 1 0 01-1-1z"/>
        <path d="M8 15a1 1 0 011-1h5a1 1 0 110 2H9a1 1 0 01-1-1z"/>
      </svg>
    @endif
  </div>
  <div class="flex-1">
    <div class="text-sm text-gray-500 dark:text-gray-400">{{ $title }}</div>
    <div class="text-2xl font-bold">{{ $value }}</div>
    @if($hint)
      <div class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">{{ $hint }}</div>
    @endif
  </div>
</div>
