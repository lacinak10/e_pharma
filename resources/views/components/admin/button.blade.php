@props([
    'variant' => 'primary', // primary|outline|danger
    'icon' => null,
    'type' => 'button',
])

@php
    $base = 'inline-flex items-center justify-center px-4 py-2 text-sm font-medium rounded-md transition';
    $cls = match($variant) {
        'primary' => $base.' bg-primary text-white hover:bg-secondary',
        'danger'  => $base.' bg-danger text-white hover:opacity-90',
        default   => $base.' border border-gray-300 text-gray-700 hover:bg-gray-50',
    };
@endphp

<button type="{{ $type }}" {{ $attributes->merge(['class' => $cls]) }}>
    @if($icon)
        <i class="{{ $icon }} mr-2"></i>
    @endif
    {{ $slot }}
</button>
