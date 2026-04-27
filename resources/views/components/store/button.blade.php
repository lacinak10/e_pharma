@props(['variant' => 'primary', 'type' => 'button'])

@php
$base = 'inline-flex items-center justify-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold transition focus:outline-none focus:ring-2 focus:ring-offset-2';
$variants = [
  'primary' => 'bg-blue-600 text-white hover:bg-blue-700 focus:ring-blue-600',
  'secondary' => 'bg-gray-900 text-white hover:bg-gray-800 focus:ring-gray-900',
  'outline' => 'bg-white text-gray-800 border border-gray-200 hover:bg-gray-50 focus:ring-blue-600',
  'danger' => 'bg-red-600 text-white hover:bg-red-700 focus:ring-red-600',
];
@endphp

<button type="{{ $type }}" {{ $attributes->merge(['class' => $base.' '.($variants[$variant] ?? $variants['primary'])]) }}>
    {{ $slot }}
</button>
