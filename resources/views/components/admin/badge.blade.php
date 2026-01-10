@props(['text' => '', 'variant' => 'gray'])

@php
    $class = match($variant) {
        'green' => 'bg-green-100 text-green-800',
        'yellow'=> 'bg-yellow-100 text-yellow-800',
        'blue'  => 'bg-blue-100 text-blue-800',
        'red'   => 'bg-red-100 text-red-800',
        default => 'bg-gray-100 text-gray-800',
    };
@endphp

<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $class }}">
    {{ $text }}
</span>
