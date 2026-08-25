@props([
    'status' => null,   // App\Enums\OrderStatus
    'tone'   => null,   // green | amber | red | blue | neutral
    'label'  => null,
])

@php
    $tone  = $tone ?? $status?->tone() ?? 'neutral';
    $label = $label ?? $status?->badge() ?? '';
@endphp

<span {{ $attributes->merge(['class' => "ep-badge ep-badge--{$tone}"]) }}>{{ $label }}{{ $slot }}</span>
