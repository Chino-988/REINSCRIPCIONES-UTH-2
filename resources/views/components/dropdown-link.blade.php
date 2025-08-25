@props(['href' => '#'])
<a href="{{ $href }}"
   class="block px-3 py-2 text-sm rounded hover:bg-gray-100 dark:hover:bg-gray-700">
  {{ $slot }}
</a>
