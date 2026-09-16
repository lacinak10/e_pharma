@extends('layouts.admin')

@section('title', 'Course ' . $order->reference . ' — ePharma')
@section('page_title', 'Course ' . $order->reference)
@section('page_subtitle', $order->client?->name . ' · ' . $order->delivery_address)

@section('content')
    <div class="ep-split">
        <div class="ep-stack">

            {{-- Action principale : l'étape suivante, en gros --}}
            @if($order->status->courierActionLabel())
                <x-ep.card>
                    <p class="ep-eyebrow">Étape suivante</p>
                    <p class="ep-h3" style="margin:.625rem 0 1.25rem">{{ $order->status->courierActionLabel() }}</p>

                    <div class="ep-row">
                        <form method="POST" action="{{ route($order->status === \App\Enums\OrderStatus::COURIER_ASSIGNED ? 'courier.my_orders.accept' : 'courier.my_orders.advance', $order) }}">
                            @csrf @method('PATCH')
                            <button type="submit" class="ep-btn ep-btn--primary">{{ $order->status->courierActionLabel() }}</button>
                        </form>

                        @if($order->status === \App\Enums\OrderStatus::COURIER_ASSIGNED)
                            <form method="POST" action="{{ route('courier.my_orders.refuse', $order) }}" class="ep-row" style="flex:1;min-width:240px">
                                @csrf @method('PATCH')
                                <input class="ep-input" name="note" maxlength="255" placeholder="Motif (facultatif)" style="flex:1;min-width:140px">
                                <button type="submit" class="ep-btn ep-btn--danger-soft">Refuser</button>
                            </form>
                        @endif
                    </div>
                </x-ep.card>
            @else
                <x-ep.card>
                    <div class="ep-row">
                        <x-ep.badge :status="$order->status" />
                        <span class="ep-small">{{ $order->status->label() }}</span>
                    </div>
                </x-ep.card>
            @endif

            <x-ep.card title="Progression de la course">
                <x-ep.timeline :order="$order" />
            </x-ep.card>

            <x-ep.card title="Médicaments à récupérer" flush>
                <div style="padding:.5rem 0">
                    @forelse($order->items as $item)
                        <div class="ep-row ep-row--nowrap" style="gap:.75rem;padding:.625rem 1.125rem">
                            <div style="flex:1;min-width:0">
                                <p style="font-size:.84375rem;font-weight:650;margin:0">
                                    {{ $item->medicine_name }}
                                    @if($item->requires_prescription)<span class="ep-badge ep-badge--rx">RX</span>@endif
                                </p>
                                <p class="ep-mono ep-small" style="margin:0">{{ $item->pack }}</p>
                            </div>
                            <span class="ep-mono" style="font-size:.9375rem">× {{ $item->quantity }}</span>
                        </div>
                    @empty
                        <p class="ep-small" style="padding:.625rem 1.125rem;margin:0">
                            Contenu défini par le manager à partir de l'ordonnance.
                        </p>
                    @endforelse
                </div>
            </x-ep.card>
        </div>

        <div class="ep-stack">
            <x-ep.card title="Retrait">
                @if($order->pharmacy)
                    <p style="font-size:.9375rem;font-weight:650;margin:0">{{ $order->pharmacy->name }}</p>
                    <p class="ep-small" style="margin:.25rem 0 .75rem">{{ $order->pharmacy->area_label }}</p>
                    <a href="tel:{{ preg_replace('/\s+/', '', $order->pharmacy->phone) }}" class="ep-btn ep-btn--ghost ep-btn--block">
                        Appeler la pharmacie
                    </a>
                @else
                    <p class="ep-small" style="margin:0">Pharmacie à confirmer par le manager.</p>
                @endif
            </x-ep.card>

            <x-ep.card title="Client">
                <p style="font-size:.9375rem;font-weight:650;margin:0">{{ $order->client?->name }}</p>
                <p class="ep-small" style="margin:.25rem 0 .75rem">{{ $order->delivery_address }}</p>
                @if($order->delivery_phone)
                    <a href="tel:{{ preg_replace('/\s+/', '', $order->delivery_phone) }}" class="ep-btn ep-btn--primary ep-btn--block">
                        Appeler le client
                    </a>
                @endif
                @if($order->delivery_code)
                    <p class="ep-small" style="margin:1rem 0 0">
                        Code de confirmation attendu :
                        <strong class="ep-mono" style="font-size:1rem">{{ $order->delivery_code }}</strong>
                    </p>
                @endif

                {{-- Le livreur doit savoir avant de partir s'il encaisse, et combien. --}}
                <p class="ep-small" style="margin:1rem 0 0">
                    Paiement : <strong>{{ $order->payment_label }}</strong>
                    @if($order->collectsCash())
                        <br>À encaisser :
                        <strong class="ep-mono" style="font-size:1rem">{{ number_format($order->total_amount, 0, ',', ' ') }} F</strong>
                    @elseif($order->isPaid())
                        <br>Réglé en ligne ({{ $order->payment?->method_label }}) — <strong>ne rien encaisser</strong>.
                    @else
                        <br>Règlement en ligne non confirmé : prévenez le manager avant de partir.
                    @endif
                </p>
                @if($order->notes)
                    <p class="ep-quote" style="margin-top:.75rem">« {{ $order->notes }} »</p>
                @endif
            </x-ep.card>

            @if($order->has_prescription && $order->prescription_path)
                <x-ep.card title="Ordonnance">
                    <a href="{{ route('courier.my_orders.prescription', $order) }}" class="ep-btn ep-btn--ghost ep-btn--block">
                        Télécharger l'ordonnance
                    </a>
                </x-ep.card>
            @endif
        </div>
    </div>
@endsection
