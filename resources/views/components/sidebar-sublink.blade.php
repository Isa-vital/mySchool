@props(['href', 'active' => false])

<a href="{{ $href }}" class="block px-3 py-1.5 text-sm rounded-md {{ $active ? 'font-medium text-gray-900 bg-gray-50' : 'text-gray-500 hover:text-gray-900 hover:bg-gray-50' }} transition-colors">
    {{ $slot }}
</a>
