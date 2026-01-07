@props([
    'name' => null,
    'type' => 'text',
])

<input
    type="{{ $type }}"
    @if($name) name="{{ $name }}" id="{{ $name }}" @endif
    {{ $attributes->merge([
        'class' => 'block w-full px-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary sm:text-sm'
    ]) }}
/>
