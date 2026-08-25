@props([
    'order',
    'square' => false,
])

@php
    /** @var \App\Models\Order $order */
    $status = $order->status;

    $meta = match (true) {
        $status === \App\Enums\OrderStatus::CHECKING => "{$order->reference} · résultat dans {$order->check_clock}",
        $status === \App\Enums\OrderStatus::TO_CLIENT && $order->eta_minutes => "{$order->reference} · arrivée dans {$order->eta_minutes} min",
        default => "{$order->reference} · " . $status->badge(),
    };
@endphp

<div {{ $attributes->merge(['class' => 'ep-banner' . ($square ? ' ep-banner--square' : '')]) }}>
    <div style="flex:1;min-width:0">
        <p class="ep-banner__title">
            @switch($status)
                @case(\App\Enums\OrderStatus::PENDING_VALIDATION) Commande en attente de validation @break
                @case(\App\Enums\OrderStatus::CHECKING) Vérification de disponibilité en cours @break
                @case(\App\Enums\OrderStatus::TO_CLIENT) Le livreur est en route vers vous @break
                @default {{ $status->badge() }}
            @endswitch
        </p>
        <p class="ep-banner__meta"
           @if($status === \App\Enums\OrderStatus::CHECKING)
               data-ep-chrono
               data-deadline="{{ $order->check_deadline_at?->toIso8601String() }}"
               data-prefix="{{ $order->reference }} · résultat dans "
           @endif>
            <span data-ep-chrono-value-inline>{{ $meta }}</span>
        </p>
    </div>
    <a href="{{ route('store.orders.show', $order) }}">Suivre ma commande</a>
</div>
