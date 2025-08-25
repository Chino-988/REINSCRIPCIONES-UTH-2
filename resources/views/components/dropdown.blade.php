@props(['align' => 'right', 'width' => '48'])

@php
$alignmentClasses = match ($align) {
  'left' => 'origin-top-left left-0',
  'right' => 'origin-top-right right-0',
  default => 'origin-top-right right-0'
};
$widthClass = match ($width) {
  '48' => 'w-48',
  '56' => 'w-56',
  default => 'w-48'
};
@endphp

<div x-data="{ open:false }" class="relative">
  <div @click="open = !open">
    {{ $trigger }}
  </div>

  <div x-cloak
       x-show="open"
       @click.away="open = false"
       @keydown.escape.window="open = false"
       class="absolute z-50 mt-2 {{ $widthClass }} rounded-md shadow-lg"
       :class="'{{ $alignmentClasses }}'">
    <div class="rounded-md ring-1 ring-black ring-opacity-5 bg-white dark:bg-gray-800 p-1">
      {{ $content }}
    </div>
  </div>
</div>
