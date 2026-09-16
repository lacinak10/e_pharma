@extends('layouts.store')

@section('title', 'Finaliser ma commande — ePharma')

@section('content')
@php
    $subtotal    = collect($cart)->sum(fn ($line) => (int) $line['price'] * (int) $line['qty']);
    $deliveryFee = 1500;
    $user        = auth()->user();
@endphp

<div class="ep-shell ep-section--tight">
    <div style="max-width:52ch;margin-bottom:2rem">
        <h1 class="ep-h2">Finaliser ma commande</h1>
        <p class="ep-lead" style="margin-top:.75rem">
            Une fois validée, votre commande part en vérification auprès de nos pharmacies partenaires.
            Vous saurez en moins de 5 minutes ce qui est disponible.
        </p>
    </div>

    <form method="POST" action="{{ route('store.checkout.store') }}">
        @csrf

        <div class="ep-split">
            <div class="ep-stack">
                <x-ep.card title="Livraison">
                    <div class="ep-grid" style="grid-template-columns:repeat(auto-fit,minmax(min(100%,240px),1fr))">
                        <div class="ep-field">
                            <label class="ep-label" for="delivery_address">Adresse de livraison</label>
                            <input class="ep-input" id="delivery_address" name="delivery_address" required
                                   minlength="5" maxlength="255"
                                   value="{{ old('delivery_address', $user->zone ? $user->zone . ', Abidjan' : '') }}"
                                   placeholder="Cocody Angré, rue des Jardins">
                            <x-input-error :messages="$errors->get('delivery_address')" class="ep-error" />
                        </div>

                        <div class="ep-field">
                            <label class="ep-label" for="delivery_phone">Téléphone</label>
                            <input class="ep-input ep-mono" id="delivery_phone" name="delivery_phone" required
                                   value="{{ old('delivery_phone', $user->phone) }}" placeholder="+2250700000000">
                            <p class="ep-hint">Le livreur vous appellera avant d'arriver.</p>
                            <x-input-error :messages="$errors->get('delivery_phone')" class="ep-error" />
                        </div>
                    </div>

                    <div class="ep-field" style="margin-top:1rem">
                        <label class="ep-label" for="notes">Précisions <span class="ep-hint">(facultatif)</span></label>
                        <textarea class="ep-textarea" id="notes" name="notes" maxlength="500"
                                  placeholder="Étage, point de repère, horaire souhaité…">{{ old('notes') }}</textarea>
                        <x-input-error :messages="$errors->get('notes')" class="ep-error" />
                    </div>
                </x-ep.card>

                <x-ep.card title="Paiement">
                    <fieldset style="border:0;padding:0;margin:0">
                        <legend class="ep-sr-only">Moyen de paiement</legend>
                        <div class="ep-grid" style="grid-template-columns:repeat(auto-fit,minmax(min(100%,160px),1fr));gap:.5rem">
                            @foreach(['cash' => 'Espèces à la remise', 'momo' => 'Mobile Money', 'card' => 'Carte bancaire'] as $value => $label)
                                <label class="ep-choice">
                                    <input type="radio" name="payment_method" value="{{ $value }}"
                                           @checked(old('payment_method', 'cash') === $value)>
                                    {{ $label }}
                                </label>
                            @endforeach
                        </div>
                        <x-input-error :messages="$errors->get('payment_method')" class="ep-error" />
                    </fieldset>

                    <p class="ep-hint" style="margin-top:1rem">
                        Rien n'est débité maintenant. Après le verdict de disponibilité, vous réglez
                        en ligne sur la page sécurisée GeniusPay — ou en espèces au livreur.
                        Le montant demandé ne portera que sur les médicaments réellement trouvés.
                    </p>
                </x-ep.card>
            </div>

            <div class="ep-stack">
                <x-ep.card title="Votre commande" flush>
                    <div style="padding:.25rem 0">
                        @foreach($cart as $line)
                            <div class="ep-row ep-row--nowrap" style="gap:.75rem;padding:.625rem 1.125rem">
                                <span style="flex:1;min-width:0;font-size:.84375rem">
                                    {{ $line['name'] }}
                                    <span class="ep-mono ep-small">× {{ $line['qty'] }}</span>
                                </span>
                                <span class="ep-mono" style="font-size:.84375rem;white-space:nowrap">
                                    {{ number_format($line['price'] * $line['qty'], 0, ',', ' ') }} F
                                </span>
                            </div>
                        @endforeach
                    </div>

                    <x-slot:footer>
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

                        <button type="submit" class="ep-btn ep-btn--primary ep-btn--block" style="margin-top:1.25rem">
                            Confirmer ma commande
                        </button>
                        <a href="{{ route('store.cart.index') }}" class="ep-btn ep-btn--ghost ep-btn--block" style="margin-top:.5rem">
                            Modifier mon panier
                        </a>
                    </x-slot:footer>
                </x-ep.card>

                <x-ep.card>
                    <p class="ep-eyebrow ep-eyebrow--amber">Ce qui suit votre confirmation</p>
                    <p class="ep-small" style="margin:.75rem 0 0">
                        « Votre commande a été prise en charge et est en attente de validation par le manager. »
                        Puis la vérification démarre : résultat annoncé en moins de 5 minutes.
                    </p>
                </x-ep.card>
            </div>
        </div>
    </form>
</div>
@endsection
