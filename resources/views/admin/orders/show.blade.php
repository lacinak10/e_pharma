@extends('layouts.admin')

@section('title', 'Commande ' . $order->reference . ' — ePharma')
@section('page_title', 'Commande ' . $order->reference)
@section('page_subtitle', $order->client?->name . ' · ' . $order->created_at->locale('fr')->isoFormat('D MMMM YYYY à HH:mm'))

@section('content')
    <div class="ep-split">
        <div class="ep-stack">

            {{-- Où en est la commande --}}
            <x-ep.card>
                <div class="ep-row" style="justify-content:space-between">
                    <div class="ep-row">
                        <x-ep.badge :status="$order->status" />
                        <span class="ep-small">{{ $order->status->label() }}</span>
                    </div>
                    <span class="ep-mono ep-small">Étape {{ $order->status->number() }} / 14 · {{ $order->status->actor() }}</span>
                </div>

                @if($order->refusal_reason)
                    <p class="ep-flash ep-flash--error" style="margin-top:1rem">Motif du refus : {{ $order->refusal_reason }}</p>
                @endif
                @if($order->cancel_reason)
                    <p class="ep-flash ep-flash--info" style="margin-top:1rem">Motif de l'annulation : {{ $order->cancel_reason }}</p>
                @endif

                @if($order->status->needsManager())
                    <a href="{{ route('manager.verifications.show', $order) }}" class="ep-btn ep-btn--primary" style="margin-top:1rem">
                        Ouvrir la fiche de vérification
                    </a>
                @endif
            </x-ep.card>

            {{-- Suivi livreur --}}
            @if($order->status->deliveryStep() !== null)
                <x-ep.card title="Progression de la livraison">
                    <x-ep.timeline :order="$order" />
                </x-ep.card>
            @endif

            {{-- Contenu --}}
            <x-ep.card title="Contenu de la commande" flush>
                <div class="ep-table-wrap">
                    <table class="ep-table ep-table--cards">
                        <thead>
                            <tr>
                                <th scope="col">Médicament</th>
                                <th scope="col">Pharmacie</th>
                                <th scope="col">Disponibilité</th>
                                <th scope="col">Qté</th>
                                <th scope="col">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($order->items as $item)
                                <tr>
                                    <td data-label="Médicament">
                                        <p style="font-size:.84375rem;font-weight:650;margin:0">
                                            {{ $item->medicine_name }}
                                            @if($item->requires_prescription)<span class="ep-badge ep-badge--rx">RX</span>@endif
                                        </p>
                                        <p class="ep-mono ep-small" style="margin:0">{{ $item->pack }}</p>
                                    </td>
                                    <td data-label="Pharmacie"><span class="ep-small">{{ $item->pharmacy?->name ?? '—' }}</span></td>
                                    <td data-label="Disponibilité">
                                        <span class="ep-badge" style="background:{{ $item->availability->tint() }};color:{{ $item->availability->color() }}">
                                            {{ $item->availability->label() }}
                                        </span>
                                    </td>
                                    <td data-label="Qté"><span class="ep-cell-num">{{ $item->quantity }}</span></td>
                                    <td data-label="Total"><span class="ep-cell-num">{{ number_format($item->line_total, 0, ',', ' ') }} F</span></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" data-label="">
                                        <span class="ep-small">Commande sur ordonnance, contenu non encore établi.</span>
                                        @if(in_array($order->status, [\App\Enums\OrderStatus::PENDING_VALIDATION, \App\Enums\OrderStatus::CHECKING], true))
                                            <a href="{{ route('manager.verifications.show', $order) }}" class="ep-btn ep-btn--ghost ep-btn--sm" style="margin-left:.5rem">
                                                Composer le panier
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <x-slot:footer>
                    @php($pending = $order->awaitsComposition())

                    <div class="ep-row ep-row--nowrap" style="justify-content:space-between">
                        <span class="ep-small">Sous-total</span>
                        <span class="ep-mono ep-small">
                            {{ $pending ? 'panier à composer' : number_format($order->subtotal, 0, ',', ' ') . ' F' }}
                        </span>
                    </div>
                    <div class="ep-row ep-row--nowrap" style="justify-content:space-between;margin-top:.375rem">
                        <span class="ep-small">Livraison</span>
                        <span class="ep-mono ep-small">{{ number_format($order->delivery_fee, 0, ',', ' ') }} F</span>
                    </div>
                    <div class="ep-row ep-row--nowrap" style="justify-content:space-between;margin-top:.625rem;padding-top:.625rem;border-top:1px solid var(--ep-rule)">
                        <strong style="font-size:.9375rem">Total</strong>
                        <strong class="ep-mono" style="font-size:1.0625rem">{{ number_format($order->total_amount, 0, ',', ' ') }} F</strong>
                    </div>
                    <div class="ep-row ep-row--nowrap" style="justify-content:space-between;margin-top:.375rem">
                        <span class="ep-small">Paiement</span>
                        <span class="ep-small">{{ $order->payment_label }}</span>
                    </div>

                    @if($order->requiresPrepayment())
                        @php($payment = $order->payment)
                        <div class="ep-row ep-row--nowrap" style="justify-content:space-between;margin-top:.375rem">
                            <span class="ep-small">Règlement en ligne</span>
                            @if($payment)
                                <x-ep.badge :tone="$payment->status->tone()" :label="$payment->status->badge()" />
                            @else
                                <x-ep.badge tone="neutral" label="Non demandé" />
                            @endif
                        </div>
                        @if($payment)
                            <p class="ep-hint ep-mono" style="margin:.375rem 0 0">
                                {{ $payment->reference }} · {{ $payment->method_label }}
                            </p>
                        @endif
                        @if($payment?->status === \App\Enums\PaymentStatus::REFUNDED)
                            <p class="ep-small" style="margin:.5rem 0 0">
                                Remboursement effectué chez GeniusPay.
                            </p>
                        @endif
                    @endif
                </x-slot:footer>
            </x-ep.card>

            {{-- Journal --}}
            <x-ep.card title="Journal de la commande" flush>
                <div class="ep-feed" style="padding:.375rem 0">
                    @forelse($order->events as $event)
                        <article class="ep-feed__item">
                            <time class="ep-feed__time" datetime="{{ $event->created_at->toIso8601String() }}">
                                {{ $event->created_at->format('H:i') }}
                            </time>
                            <div style="min-width:0">
                                <p class="ep-feed__text">{{ $event->message }}</p>
                                <span class="ep-feed__tag" style="color:{{ $event->color }}">
                                    {{ $event->tag }}@if($event->author) · {{ $event->author->short_name }}@endif
                                </span>
                            </div>
                        </article>
                    @empty
                        <p class="ep-small" style="padding:.75rem 1rem;margin:0">Aucun événement.</p>
                    @endforelse
                </div>
            </x-ep.card>
        </div>

        <div class="ep-stack">
            <x-ep.card title="Client">
                <p style="font-size:.9375rem;font-weight:650;margin:0">{{ $order->client?->name }}</p>
                <p class="ep-small" style="margin:.25rem 0 0">{{ $order->delivery_address }}</p>
                @if($order->delivery_phone)
                    <a href="tel:{{ preg_replace('/\s+/', '', $order->delivery_phone) }}" class="ep-mono ep-small">{{ $order->delivery_phone }}</a>
                @endif
                @if($order->notes)
                    <p class="ep-quote" style="margin-top:.75rem">« {{ $order->notes }} »</p>
                @endif
            </x-ep.card>

            @if($order->assignment?->courier)
                <x-ep.card title="Livreur">
                    <x-ep.courier-card :courier="$order->assignment->courier" :eta="$order->eta_minutes" />
                    @if($order->delivery_code)
                        <p class="ep-small" style="margin:1rem 0 0">
                            Code de confirmation : <strong class="ep-mono">{{ $order->delivery_code }}</strong>
                        </p>
                    @endif
                </x-ep.card>
            @elseif($order->status === \App\Enums\OrderStatus::AVAILABLE || $order->status === \App\Enums\OrderStatus::PARTIALLY_AVAILABLE)
                <x-ep.card title="Attribuer un livreur">
                    <form method="POST" action="{{ route('manager.assignments.store') }}" class="ep-stack" style="gap:.75rem">
                        @csrf
                        <input type="hidden" name="order_id" value="{{ $order->id }}">
                        <div class="ep-field">
                            <label class="ep-label" for="courier_id">Livreur</label>
                            <select class="ep-select" id="courier_id" name="courier_id" required>
                                @foreach($couriers as $courier)
                                    <option value="{{ $courier->id }}">{{ $courier->name }} — {{ $courier->phone }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="ep-field">
                            <label class="ep-label" for="eta_minutes">Délai estimé (min)</label>
                            <input class="ep-input ep-mono" id="eta_minutes" type="number" name="eta_minutes" min="5" max="180" placeholder="auto">
                        </div>
                        <button type="submit" class="ep-btn ep-btn--primary ep-btn--block">Attribuer</button>
                    </form>
                </x-ep.card>
            @endif

            @if($order->has_prescription)
                <x-ep.prescription-viewer
                    :order="$order"
                    :download-url="$order->prescription_path ? route('manager.orders.prescription', $order) : null" />
            @endif

            @if($order->review)
                <x-ep.card title="Avis du client">
                    <div class="ep-row ep-row--nowrap" style="justify-content:space-between">
                        <span class="ep-stars" style="font-size:1rem;letter-spacing:1.5px">{{ $order->review->stars }}</span>
                        <time class="ep-mono ep-small">{{ $order->review->created_at->locale('fr')->isoFormat('D MMM YYYY') }}</time>
                    </div>
                    @if($order->review->comment)
                        <p class="ep-body" style="margin:.875rem 0 0">« {{ $order->review->comment }} »</p>
                    @endif
                </x-ep.card>
            @endif

            @can('cancel', $order)
                <x-ep.card title="Annuler la commande">
                    <form method="POST" action="{{ route('manager.orders.cancel', $order) }}">
                        @csrf
                        <div class="ep-field">
                            <label class="ep-label" for="cancel-reason">Motif</label>
                            <input class="ep-input" id="cancel-reason" name="reason" maxlength="255" placeholder="Motif communiqué au client">
                        </div>
                        <button type="submit" class="ep-btn ep-btn--danger-soft ep-btn--block" style="margin-top:.75rem">
                            Annuler la commande
                        </button>
                    </form>
                </x-ep.card>
            @endcan
        </div>
    </div>
@endsection
