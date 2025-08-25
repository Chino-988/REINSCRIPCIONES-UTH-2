@props(['href' => '#', 'active' => false])

@php
$classes = $active
    ? 'block px-3 py-2 rounded-lg bg-uth-600 text-white'
    : 'block px-3 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700';
@endphp

<a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
  {{ $slot }}
</a>
