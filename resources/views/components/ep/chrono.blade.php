@props([
    'order',
    'urgent'  => false,   // variante manager : rouge sous 1:00
    'caption' => 'restantes avant le résultat de disponibilité',
])

@php
    /** @var \App\Models\Order $order */
    $left     = $order->secondsLeftForCheck();
    $deadline = $order->check_deadline_at?->toIso8601String();
    // Côté client le chiffre reste ambre : on ne panique pas le patient.
    $isUrgent = $urgent && $left <= 60;
@endphp

<div {{ $attributes->merge(['class' => 'ep-chrono' . ($isUrgent ? ' ep-chrono--urgent' : '')]) }}
     data-ep-chrono
     data-deadline="{{ $deadline }}"
     data-duration="{{ \App\Models\Order::CHECK_DURATION_SECONDS }}"
     @if($urgent) data-urgent="true" @endif>

    <p class="ep-chrono__label">Vérification auprès des partenaires</p>

    <div class="ep-chrono__body">
        <span class="ep-chrono__value" data-ep-chrono-value>{{ $order->check_clock }}</span>
        <span class="ep-chrono__caption">{{ $caption }}</span>
    </div>

    <div class="ep-chrono__track">
        <div class="ep-chrono__fill" data-ep-chrono-bar style="width: {{ $order->check_progress }}%"></div>
    </div>

    @isset($actions)
        <div class="ep-row" style="margin-top: 1.5rem">{{ $actions }}</div>
    @endisset
</div>
