@props(['order'])

@php
    /** @var \App\Models\Order $order */
    $status = $order->status;
    $failed = in_array($status, [
        \App\Enums\OrderStatus::REFUSED,
        \App\Enums\OrderStatus::UNAVAILABLE,
        \App\Enums\OrderStatus::CANCELED,
    ], true);
@endphp

<x-ep.card title="Suivi de votre commande" {{ $attributes }}>
    <x-slot:actions>
        <x-ep.badge :status="$status" />
    </x-slot:actions>

    @if($failed)
        <p class="ep-flash ep-flash--error" style="margin-bottom:1rem">
            {{ $status->label() }}
            @if($order->refusal_reason) — {{ $order->refusal_reason }} @endif
            @if($order->cancel_reason) — {{ $order->cancel_reason }} @endif
        </p>
    @elseif($status->deliveryStep() !== null)
        {{-- Les cinq étapes de la course --}}
        <x-ep.timeline :order="$order" />
    @else
        {{-- Avant l'attribution : on montre où en est le dossier --}}
        <ol class="ep-timeline" style="list-style:none;margin:0;padding:0">
            @foreach([
                \App\Enums\OrderStatus::PENDING_VALIDATION,
                \App\Enums\OrderStatus::CHECKING,
                \App\Enums\OrderStatus::AVAILABLE,
                \App\Enums\OrderStatus::COURIER_ASSIGNED,
            ] as $step)
                @php
                    $state = match (true) {
                        $step->number() < $status->number()   => 'done',
                        $step->number() === $status->number() => 'current',
                        default                               => 'todo',
                    };
                @endphp
                <li class="ep-timeline__step ep-timeline__step--{{ $state }}">
                    <span class="ep-timeline__rail" aria-hidden="true">
                        <span class="ep-timeline__dot"></span>
                        <span class="ep-timeline__line"></span>
                    </span>
                    <div class="ep-timeline__content">
                        <p class="ep-timeline__label">{{ $step->badge() }}</p>
                        <p class="ep-timeline__sub">{{ $step->label() }}</p>
                    </div>
                    <span class="ep-timeline__time">
                        {{ $state === 'done' ? '✓' : ($state === 'current' ? 'en cours' : '—') }}
                    </span>
                </li>
            @endforeach
        </ol>
    @endif
</x-ep.card>
