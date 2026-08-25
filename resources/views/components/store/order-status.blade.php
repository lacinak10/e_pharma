@props(['status'])

@php
    $status = $status instanceof \App\Enums\OrderStatus
        ? $status
        : \App\Enums\OrderStatus::tryFrom(strtoupper((string) $status));
@endphp

@if($status)
    <x-ep.badge :status="$status" {{ $attributes }} />
@endif
