@extends('layouts.store')

@section('title', 'Mes commandes — ePharma')

@section('content')
<div class="ep-shell ep-section--tight">
    <h1 class="ep-h2" style="margin-bottom:1.5rem">Mes commandes</h1>

    @forelse($orders as $order)
        <a href="{{ route('store.orders.show', $order) }}" class="ep-card"
           style="display:block;margin-bottom:.875rem;padding:1.125rem;color:inherit;text-decoration:none">
            <div class="ep-row ep-row--nowrap" style="justify-content:space-between;gap:.75rem">
                <div style="min-width:0">
                    <p class="ep-mono" style="font-size:.8125rem;color:var(--ep-green);margin:0">{{ $order->reference }}</p>
                    <p style="font-size:.9375rem;font-weight:650;margin:.25rem 0 0">
                        {{ $order->has_prescription ? 'Commande sur ordonnance' : $order->items_summary }}
                    </p>
                    <p class="ep-small" style="margin:.25rem 0 0">
                        {{ $order->created_at->locale('fr')->isoFormat('D MMM YYYY · HH:mm') }}
                    </p>
                </div>
                <div style="text-align:right;flex:none">
                    <x-ep.badge :status="$order->status" />
                    <p class="ep-mono" style="font-size:.9375rem;font-weight:600;margin:.5rem 0 0">
                        {{ number_format($order->total_amount, 0, ',', ' ') }} F
                    </p>
                </div>
            </div>

            @if($order->status === \App\Enums\OrderStatus::CHECKING)
                <p class="ep-small" style="margin:.75rem 0 0;color:var(--ep-amber)"
                   data-ep-chrono data-deadline="{{ $order->check_deadline_at?->toIso8601String() }}"
                   data-prefix="Résultat de disponibilité dans ">
                    <span data-ep-chrono-value-inline>Résultat de disponibilité dans {{ $order->check_clock }}</span>
                </p>
            @elseif($order->awaitsReview())
                <p class="ep-small" style="margin:.75rem 0 0;color:var(--ep-green)">
                    Notez votre livreur →
                </p>
            @endif
        </a>
    @empty
        <x-ep.card>
            <x-ep.empty title="Rien en cours."
                        text="Cherchez un médicament ou envoyez une ordonnance.">
                <x-slot:actions>
                    <a href="{{ route('store.medicines.index') }}" class="ep-btn ep-btn--primary">Voir les médicaments</a>
                    <a href="{{ route('store.prescriptions.create') }}" class="ep-btn ep-btn--ghost">Envoyer une ordonnance</a>
                </x-slot:actions>
            </x-ep.empty>
        </x-ep.card>
    @endforelse

    @if($orders->hasPages())
        <div style="margin-top:1.5rem">{{ $orders->links() }}</div>
    @endif
</div>
@endsection
