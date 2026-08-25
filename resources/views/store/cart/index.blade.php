@extends('layouts.store')

@section('title', 'Mon panier — ePharma')

@section('content')
@php
    $subtotal    = collect($cart)->sum(fn ($line) => (int) $line['price'] * (int) $line['qty']);
    $deliveryFee = 1500;
    $hasRx       = \App\Models\Medicine::whereIn('id', array_keys($cart))
        ->where('requires_prescription', true)->exists();
@endphp

<div class="ep-shell ep-section--tight">
    <h1 class="ep-h2" style="margin-bottom:1.5rem">Mon panier</h1>

    @if(empty($cart))
        <x-ep.card>
            <x-ep.empty title="Votre panier est vide."
                        text="Cherchez un médicament ou envoyez une ordonnance.">
                <x-slot:actions>
                    <a href="{{ route('store.medicines.index') }}" class="ep-btn ep-btn--primary">Voir les médicaments</a>
                    <a href="{{ route('store.prescriptions.create') }}" class="ep-btn ep-btn--ghost">Envoyer une ordonnance</a>
                </x-slot:actions>
            </x-ep.empty>
        </x-ep.card>
    @else
        <div class="ep-split">
            <div class="ep-stack">
                <x-ep.card flush>
                    <header class="ep-card__head">
                        <h2 class="ep-card__title">{{ count($cart) }} médicament{{ count($cart) > 1 ? 's' : '' }}</h2>
                        <form method="POST" action="{{ route('store.cart.clear') }}" class="ep-spacer">
                            @csrf @method('DELETE')
                            <button type="submit" class="ep-btn ep-btn--ghost ep-btn--sm">Vider le panier</button>
                        </form>
                    </header>

                    <div style="padding:.25rem 0">
                        @foreach($cart as $line)
                            @php $medicine = \App\Models\Medicine::find($line['id']); @endphp
                            <div class="ep-row ep-row--nowrap"
                                 style="gap:1rem;padding:1rem 1.125rem;border-bottom:1px solid var(--ep-rule-soft);align-items:flex-start;flex-wrap:wrap">
                                <img src="{{ $medicine?->image_src ?? asset('assets/images/photos/medicaments.jpg') }}" alt=""
                                     style="width:64px;height:64px;object-fit:cover;border-radius:var(--ep-radius-sm);flex:none">

                                <div style="flex:1;min-width:140px">
                                    <a href="{{ $medicine ? route('store.medicines.show', $medicine) : '#' }}"
                                       style="font-size:.90625rem;font-weight:650;color:var(--ep-ink)">{{ $line['name'] }}</a>
                                    @if($medicine?->requires_prescription)
                                        <span class="ep-badge ep-badge--rx" style="margin-left:.375rem">Sur ordonnance</span>
                                    @endif
                                    <p class="ep-mono ep-small" style="margin:.25rem 0 0">
                                        {{ number_format($line['price'], 0, ',', ' ') }} F l'unité (indicatif)
                                        @if($medicine) · {{ $medicine->pack_label }} @endif
                                    </p>
                                </div>

                                <form method="POST" action="{{ route('store.cart.update', $line['id']) }}"
                                      class="ep-row ep-row--nowrap" style="gap:.375rem;flex:none">
                                    @csrf @method('PATCH')
                                    <label class="ep-sr-only" for="qty-{{ $line['id'] }}">Quantité pour {{ $line['name'] }}</label>
                                    <input class="ep-input ep-mono" id="qty-{{ $line['id'] }}" type="number" name="qty"
                                           value="{{ $line['qty'] }}" min="1" max="99"
                                           style="width:76px;padding:.5rem" onchange="this.form.submit()">
                                    <noscript><button type="submit" class="ep-btn ep-btn--ghost ep-btn--sm">OK</button></noscript>
                                </form>

                                <span class="ep-mono" style="font-size:.9375rem;font-weight:600;min-width:88px;text-align:right;flex:none">
                                    {{ number_format($line['price'] * $line['qty'], 0, ',', ' ') }} F
                                </span>

                                <form method="POST" action="{{ route('store.cart.remove', $line['id']) }}" style="flex:none">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="ep-btn ep-btn--ghost ep-btn--sm"
                                            aria-label="Retirer {{ $line['name'] }} du panier">Retirer</button>
                                </form>
                            </div>
                        @endforeach
                    </div>
                </x-ep.card>

                @if($hasRx)
                    <p class="ep-flash ep-flash--info">
                        Votre panier contient un médicament sur ordonnance.
                        <a href="{{ route('store.prescriptions.create') }}">Téléversez-la</a> pour que le manager
                        puisse la valider.
                    </p>
                @endif

                <a href="{{ route('store.medicines.index') }}" style="font-size:.90625rem;font-weight:650">
                    ← Continuer mes achats
                </a>
            </div>

            <div class="ep-stack">
                <x-ep.card title="Récapitulatif">
                    <div class="ep-row ep-row--nowrap" style="justify-content:space-between">
                        <span class="ep-small">Sous-total</span>
                        <span class="ep-mono ep-small">{{ number_format($subtotal, 0, ',', ' ') }} F</span>
                    </div>
                    <div class="ep-row ep-row--nowrap" style="justify-content:space-between;margin-top:.5rem">
                        <span class="ep-small">Livraison</span>
                        <span class="ep-mono ep-small">{{ number_format($deliveryFee, 0, ',', ' ') }} F</span>
                    </div>
                    <div class="ep-row ep-row--nowrap"
                         style="justify-content:space-between;margin-top:.875rem;padding-top:.875rem;border-top:1px solid var(--ep-rule)">
                        <strong style="font-size:.9375rem">Total</strong>
                        <strong class="ep-mono" style="font-size:1.125rem">{{ number_format($subtotal + $deliveryFee, 0, ',', ' ') }} F</strong>
                    </div>

                    <x-slot:footer>
                        <a href="{{ route('store.checkout.create') }}" class="ep-btn ep-btn--primary ep-btn--block">
                            Passer la commande
                        </a>
                        <p class="ep-hint" style="text-align:center;margin-top:.75rem">
                            Montants indicatifs : ils sont ajustés après confirmation
                            de disponibilité par nos pharmacies partenaires.
                        </p>
                    </x-slot:footer>
                </x-ep.card>

                <x-ep.card>
                    <p class="ep-eyebrow ep-eyebrow--amber">Après validation</p>
                    <p class="ep-small" style="margin:.75rem 0 0">
                        Un manager appelle nos pharmacies partenaires et vous confirme la disponibilité
                        <strong>en moins de 5 minutes</strong>. Si un médicament manque, une alternative
                        vous est proposée avant tout déplacement.
                    </p>
                </x-ep.card>
            </div>
        </div>
    @endif
</div>
@endsection
