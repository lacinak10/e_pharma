@extends('layouts.admin')

@section('title', 'Attribution des livreurs — ePharma')
@section('page_title', 'Attribution du livreur')
@section('page_subtitle', 'Commandes validées, en attente d’un livreur')

@section('content')
    @forelse($orders as $order)
        <x-ep.card flush>
            <header class="ep-card__head">
                <h2 class="ep-card__title">{{ $order->reference }}</h2>
                <span class="ep-mono ep-small">
                    {{ $order->client?->short_name }} · {{ $order->delivery_address }}
                    @if($order->pharmacy) · départ {{ $order->pharmacy->name }} @endif
                </span>
                <span class="ep-spacer">
                    <x-ep.badge :status="$order->status" />
                    @if($order->requiresPrepayment())
                        <x-ep.badge :tone="$order->isPaid() ? 'green' : 'amber'"
                                    :label="$order->isPaid() ? 'Payée en ligne' : 'Paiement en attente'" />
                    @endif
                </span>
            </header>

            @if($order->awaitsPayment())
                {{-- Le livreur avance l'argent en pharmacie : OrderWorkflow refuse
                     l'attribution tant que l'encaissement n'est pas confirmé. --}}
                <p class="ep-small" style="padding:.75rem 1.125rem;margin:0;background:#FBF0DC">
                    Cette commande se règle en ligne et n'est pas encore payée. L'attribution
                    reste bloquée jusqu'à confirmation de l'encaissement.
                </p>
            @endif

            <div class="ep-table-wrap">
                <table class="ep-table ep-table--cards">
                    <thead>
                        <tr>
                            <th scope="col" colspan="2">Livreur</th>
                            <th scope="col">Distance</th>
                            <th scope="col">État</th>
                            <th scope="col">Note</th>
                            <th scope="col">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($couriers as $index => $courier)
                            <tr @if($index === 0) style="background:#F6FBF8" @endif>
                                <td data-label="" style="width:44px">
                                    <img src="{{ $courier->avatar_url }}" alt=""
                                         style="width:36px;height:36px;border-radius:50%;object-fit:cover">
                                </td>
                                <td data-label="Livreur">
                                    <p style="font-size:.84375rem;font-weight:650;display:flex;align-items:center;gap:.5rem;margin:0">
                                        {{ $courier->short_name }}
                                        @if($index === 0)<span class="ep-badge ep-badge--green">Suggéré</span>@endif
                                    </p>
                                    <p class="ep-mono ep-small" style="margin:0">{{ $courier->phone ?? '—' }}</p>
                                </td>
                                <td data-label="Distance">
                                    <span class="ep-cell-num" style="color:var(--ep-blue)">
                                        {{ $courier->distance_km !== null ? number_format($courier->distance_km, 1, ',', ' ') . ' km' : '—' }}
                                    </span>
                                </td>
                                <td data-label="État">
                                    @php $tone = $courier->state === 'Disponible' ? 'green' : ($courier->state === 'En pharmacie' ? 'amber' : 'blue'); @endphp
                                    <x-ep.badge :tone="$tone" :label="$courier->state" />
                                </td>
                                <td data-label="Note">
                                    <span class="ep-row ep-row--nowrap" style="gap:.5rem">
                                        <span class="ep-stars" aria-hidden="true">{{ $courier->stars }}</span>
                                        <span class="ep-mono ep-small">
                                            {{ $courier->rating ? number_format($courier->rating, 1, ',', ' ') : '—' }} ·
                                            {{ $courier->load }} course(s)
                                        </span>
                                    </span>
                                </td>
                                <td data-label="Action">
                                    <form method="POST" action="{{ route('manager.assignments.store') }}" class="ep-row" style="gap:.375rem">
                                        @csrf
                                        <input type="hidden" name="order_id" value="{{ $order->id }}">
                                        <input type="hidden" name="courier_id" value="{{ $courier->id }}">
                                        <label class="ep-sr-only" for="eta-{{ $order->id }}-{{ $courier->id }}">Délai estimé</label>
                                        <input class="ep-input" style="width:74px;padding:.375rem .5rem;font-size:.75rem"
                                               id="eta-{{ $order->id }}-{{ $courier->id }}" type="number" name="eta_minutes"
                                               min="5" max="180" placeholder="min">
                                        <button type="submit" class="ep-btn ep-btn--sm {{ $index === 0 ? 'ep-btn--primary' : 'ep-btn--ghost' }}"
                                                @disabled($courier->state === 'Indisponible' || $order->awaitsPayment())>Attribuer</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-ep.card>
    @empty
        <x-ep.card>
            <x-ep.empty title="Aucune commande à attribuer."
                        text="Les commandes dont la disponibilité est confirmée arrivent ici pour être confiées à un livreur.">
                <x-slot:actions>
                    <a href="{{ route('manager.verifications.index') }}" class="ep-btn ep-btn--ghost">Voir les vérifications</a>
                </x-slot:actions>
            </x-ep.empty>
        </x-ep.card>
    @endforelse
@endsection
