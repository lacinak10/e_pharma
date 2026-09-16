@extends('layouts.store')

@section('title', 'Commande ' . $order->reference . ' — ePharma')

@section('content')
@php
    use App\Enums\OrderStatus;
    $status = $order->status;
    $courier = $order->assignment?->courier;
@endphp

<div class="ep-shell ep-section--tight" @if($status === OrderStatus::CHECKING) data-ep-live-refresh="60" @endif>

    <nav class="ep-small" style="margin-bottom:1rem">
        <a href="{{ route('store.orders.index') }}">← Mes commandes</a>
    </nav>

    <div class="ep-row" style="justify-content:space-between;align-items:flex-start;margin-bottom:1.5rem">
        <div>
            <h1 class="ep-h2">Commande {{ $order->reference }}</h1>
            <p class="ep-small" style="margin-top:.375rem">
                Passée le {{ $order->created_at->locale('fr')->isoFormat('D MMMM YYYY à HH:mm') }}
            </p>
        </div>
        <x-ep.badge :status="$status" />
    </div>

    <div class="ep-split">
        <div class="ep-stack">

            {{-- Étape 3 : le compte à rebours annoncé au client --}}
            @if($status === OrderStatus::CHECKING)
                <x-ep.chrono :order="$order" caption="restantes avant le résultat de disponibilité" />
                <p class="ep-body" style="margin:0">
                    Vos médicaments sont en cours de vérification auprès de nos pharmacies partenaires
                    afin de confirmer leur disponibilité. Vous serez informé du résultat dans moins de 5 minutes.
                </p>

            @elseif($status === OrderStatus::PENDING_VALIDATION)
                <x-ep.card>
                    <p class="ep-eyebrow ep-eyebrow--amber">En attente de validation</p>
                    <p class="ep-body" style="margin:.75rem 0 0">
                        Votre commande a été prise en charge et est en attente de validation par le manager.
                    </p>
                </x-ep.card>

            @elseif($status === OrderStatus::UNAVAILABLE)
                <x-ep.card>
                    <p class="ep-eyebrow" style="color:var(--ep-red)">Indisponible</p>
                    <p class="ep-body" style="margin:.75rem 0 0">
                        Vos médicaments sont introuvables chez nos partenaires pour le moment.
                        Consultez les alternatives du catalogue ou réessayez plus tard.
                    </p>
                    <a href="{{ route('store.medicines.index') }}" class="ep-btn ep-btn--ghost" style="margin-top:1rem">
                        Voir les alternatives
                    </a>
                </x-ep.card>
            @endif

            {{-- Le livreur assigné : nom, numéro, délai --}}
            @if($courier && $status->isCourierPhase())
                <x-ep.card title="Votre livreur">
                    <x-ep.courier-card :courier="$courier" :eta="$order->eta_minutes" />
                    @if($order->delivery_code)
                        <p class="ep-small" style="margin:1rem 0 0">
                            Code de confirmation à donner au livreur :
                            <strong class="ep-mono" style="font-size:1rem">{{ $order->delivery_code }}</strong>
                        </p>
                    @endif
                </x-ep.card>
            @endif

            {{-- Le suivi : 5 étapes ou avancement du dossier --}}
            <x-store.order-timeline :order="$order" />

            {{-- Étape 13 : notation du livreur --}}
            @if($order->awaitsReview() && $courier)
                <x-ep.card title="Notez votre livreur">
                    <x-ep.rating-form :order="$order" />
                </x-ep.card>
            @elseif($order->review)
                <x-ep.card title="Votre avis">
                    <div class="ep-row ep-row--nowrap" style="justify-content:space-between">
                        <span class="ep-stars" style="font-size:1rem;letter-spacing:1.5px">{{ $order->review->stars }}</span>
                        <time class="ep-mono ep-small">{{ $order->review->created_at->locale('fr')->isoFormat('D MMM YYYY') }}</time>
                    </div>
                    @if($order->review->comment)
                        <p class="ep-body" style="margin:.875rem 0 0">« {{ $order->review->comment }} »</p>
                    @endif
                </x-ep.card>
            @endif
        </div>

        <div class="ep-stack">
            {{-- Contenu de la commande --}}
            <x-ep.card title="Votre commande" flush>
                <div style="padding:.5rem 0">
                    @forelse($order->items as $item)
                        <div class="ep-row ep-row--nowrap" style="gap:.75rem;padding:.75rem 1.125rem;align-items:flex-start">
                            <div style="flex:1;min-width:0">
                                <p style="font-size:.84375rem;font-weight:650;margin:0">{{ $item->medicine_name }}</p>
                                <p class="ep-mono ep-small" style="margin:.125rem 0 0">
                                    {{ $item->quantity }} × {{ number_format($item->unit_price, 0, ',', ' ') }} F
                                </p>
                                @if($item->availability->isSettled())
                                    <span class="ep-badge" style="margin-top:.375rem;background:{{ $item->availability->tint() }};color:{{ $item->availability->color() }}">
                                        {{ $item->availability->label() }}
                                    </span>
                                @endif
                                @if($item->substitute_name)
                                    <p class="ep-small" style="margin:.25rem 0 0">Substitut proposé : {{ $item->substitute_name }}</p>
                                @endif
                            </div>
                            <span class="ep-mono" style="font-size:.84375rem;white-space:nowrap">
                                {{ number_format($item->line_total, 0, ',', ' ') }} F
                            </span>
                        </div>
                    @empty
                        <p class="ep-small" style="padding:.75rem 1.125rem;margin:0">
                            Commande sur ordonnance : le contenu sera établi après lecture par le manager.
                        </p>
                    @endforelse
                </div>

                <x-slot:footer>
                    {{-- Une ordonnance arrive sans ligne : tant que le manager n'a pas
                         composé le panier, le montant est inconnu, pas nul. --}}
                    @php($pending = $order->awaitsComposition())

                    <div class="ep-row ep-row--nowrap" style="justify-content:space-between">
                        <span class="ep-small">Sous-total</span>
                        <span class="ep-mono ep-small">
                            {{ $pending ? 'à établir' : number_format($order->subtotal, 0, ',', ' ') . ' F' }}
                        </span>
                    </div>
                    <div class="ep-row ep-row--nowrap" style="justify-content:space-between;margin-top:.375rem">
                        <span class="ep-small">Livraison</span>
                        <span class="ep-mono ep-small">{{ number_format($order->delivery_fee, 0, ',', ' ') }} F</span>
                    </div>
                    <div class="ep-row ep-row--nowrap" style="justify-content:space-between;margin-top:.625rem;padding-top:.625rem;border-top:1px solid var(--ep-rule)">
                        <strong style="font-size:.9375rem">Total</strong>
                        <strong class="ep-mono" style="font-size:1.0625rem">
                            {{ $pending ? 'à établir' : number_format($order->total_amount, 0, ',', ' ') . ' F' }}
                        </strong>
                    </div>
                    @if($pending)
                        <p class="ep-hint" style="margin:.5rem 0 0">
                            Le manager lit votre ordonnance et compose votre panier.
                            Le montant s'affichera ici dès qu'il sera établi.
                        </p>
                    @endif
                    <div class="ep-row ep-row--nowrap" style="justify-content:space-between;margin-top:.375rem">
                        <span class="ep-small">Paiement</span>
                        <span class="ep-small">{{ $order->payment_label }}</span>
                    </div>

                    {{-- Règlement en ligne. Libellés et couleurs viennent de
                         PaymentStatus : aucune correspondance locale ici. --}}
                    @if($order->requiresPrepayment())
                        @php($payment = $order->payment)

                        <div class="ep-row ep-row--nowrap" style="justify-content:space-between;margin-top:.375rem">
                            <span class="ep-small">État du règlement</span>
                            @if($payment)
                                <x-ep.badge :tone="$payment->status->tone()" :label="$payment->status->badge()" />
                            @else
                                <x-ep.badge tone="neutral" label="Après le verdict" />
                            @endif
                        </div>

                        @if($payment)
                            <p class="ep-small" style="margin:.625rem 0 0">{{ $payment->status->label() }}</p>
                        @endif

                        @can('pay', $order)
                            <form method="POST" action="{{ route('store.payments.pay', $order) }}" style="margin-top:.875rem">
                                @csrf
                                <button type="submit" class="ep-btn ep-btn--primary ep-btn--block">
                                    {{ $payment?->status->isRetryable() ? 'Reprendre le paiement' : 'Payer maintenant' }} ·
                                    {{ number_format($order->total_amount, 0, ',', ' ') }} F
                                </button>
                            </form>
                            <p class="ep-hint" style="margin:.5rem 0 0">
                                Wave, Orange Money, MTN, Moov ou carte — le choix se fait sur la page sécurisée GeniusPay.
                            </p>
                        @endcan
                    @endif
                </x-slot:footer>
            </x-ep.card>

            {{-- Ordonnance --}}
            @if($order->has_prescription)
                <x-ep.prescription-viewer
                    :order="$order"
                    :download-url="$order->prescription_path ? route('store.prescriptions.download', $order) : null" />
            @endif

            {{-- Livraison --}}
            <x-ep.card title="Livraison">
                <p style="font-size:.875rem;margin:0">{{ $order->delivery_address }}</p>
                @if($order->delivery_phone)
                    <p class="ep-mono ep-small" style="margin:.25rem 0 0">{{ $order->delivery_phone }}</p>
                @endif
                @if($order->pharmacy)
                    <p class="ep-small" style="margin:.75rem 0 0">
                        Retrait chez <strong>{{ $order->pharmacy->name }}</strong> — {{ $order->pharmacy->area }}
                    </p>
                @endif

                @can('cancel', $order)
                    <form method="POST" action="{{ route('store.orders.cancel', $order) }}" style="margin-top:1rem">
                        @csrf
                        <label class="ep-sr-only" for="cancel-reason">Motif</label>
                        <input class="ep-input" id="cancel-reason" name="reason" maxlength="255"
                               placeholder="Motif (facultatif)" style="margin-bottom:.5rem">
                        <button type="submit" class="ep-btn ep-btn--danger-soft ep-btn--block">Annuler ma commande</button>
                    </form>
                @endcan
            </x-ep.card>
        </div>
    </div>
</div>
@endsection
