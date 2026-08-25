@extends('layouts.admin')

@section('title', 'Vérification ' . $order->reference . ' — ePharma')
@section('page_title', 'Vérification ' . $order->reference)
@section('page_subtitle', $order->client?->name . ' · ' . $order->delivery_address)

@section('content')
    @php
        // Le contenu reste modifiable tant que le verdict n'est pas rendu.
        $composable = in_array($order->status, [
            \App\Enums\OrderStatus::PENDING_VALIDATION,
            \App\Enums\OrderStatus::CHECKING,
        ], true);
    @endphp

    <div class="ep-split">
        <div class="ep-stack">

            @if($order->status === \App\Enums\OrderStatus::CHECKING)
                <x-ep.chrono :order="$order" urgent caption="restantes avant le résultat annoncé au client">
                    <x-slot:actions>
                        <form method="POST" action="{{ route('manager.verifications.settle', $order) }}">
                            @csrf
                            <button type="submit" class="ep-btn ep-btn--primary">Rendre le verdict</button>
                        </form>
                    </x-slot:actions>
                </x-ep.chrono>
            @elseif($order->status === \App\Enums\OrderStatus::PENDING_VALIDATION)
                <x-ep.card>
                    <p class="ep-body" style="margin:0 0 1rem">
                        Cette commande est en attente de votre validation. En la lançant, le client reçoit
                        aussitôt : « Vos médicaments sont en cours de vérification auprès de nos pharmacies
                        partenaires. Vous serez informé du résultat dans moins de 5 minutes. »
                    </p>
                    <form method="POST" action="{{ route('manager.verifications.start', $order) }}">
                        @csrf
                        <button type="submit" class="ep-btn ep-btn--primary">Lancer la vérification (5:00)</button>
                    </form>
                </x-ep.card>
            @else
                <x-ep.card>
                    <div class="ep-row">
                        <x-ep.badge :status="$order->status" />
                        <span class="ep-small">{{ $order->status->label() }}</span>
                    </div>
                </x-ep.card>
            @endif

            {{-- Lignes de la commande : le verdict de chaque pharmacie --}}
            <x-ep.card title="Médicaments à confirmer" flush>
                <div class="ep-table-wrap">
                    <table class="ep-table ep-table--cards">
                        <thead>
                            <tr>
                                <th scope="col">Médicament</th>
                                <th scope="col">Pharmacie contactée</th>
                                <th scope="col">Verdict</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($order->items as $item)
                                <tr>
                                    <td class="ep-cell-stack" data-label="Médicament">
                                        <p style="font-size:.875rem;font-weight:650;display:flex;align-items:center;gap:.5rem;margin:0">
                                            {{ $item->medicine_name }}
                                            @if($item->requires_prescription)<span class="ep-badge ep-badge--rx">RX</span>@endif
                                        </p>
                                        <p class="ep-mono ep-small" style="margin:3px 0 0">
                                            {{ $item->pack }} · {{ $item->quantity }} ×
                                            {{ number_format($item->unit_price, 0, ',', ' ') }} F
                                        </p>
                                        @if($item->substitute_name)
                                            <p class="ep-small" style="margin:.25rem 0 0;color:var(--ep-amber)">
                                                Substitut : {{ $item->substitute_name }}
                                            </p>
                                        @endif
                                    </td>
                                    <td class="ep-cell-stack" data-label="Pharmacie" colspan="2">
                                        <form method="POST" action="{{ route('manager.verifications.item', [$order, $item]) }}"
                                              class="ep-row" style="gap:.5rem;align-items:flex-end">
                                            @csrf @method('PATCH')

                                            <div class="ep-field" style="flex:1;min-width:170px">
                                                <label class="ep-sr-only" for="ph-{{ $item->id }}">Pharmacie</label>
                                                <select class="ep-select" id="ph-{{ $item->id }}" name="pharmacy_id">
                                                    <option value="">Choisir une pharmacie…</option>
                                                    @foreach($pharmacies as $pharmacy)
                                                        <option value="{{ $pharmacy->id }}" @selected($item->pharmacy_id === $pharmacy->id)>
                                                            {{ $pharmacy->name }} — {{ $pharmacy->phone }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div class="ep-field" style="flex:1;min-width:150px">
                                                <label class="ep-sr-only" for="av-{{ $item->id }}">Verdict</label>
                                                <select class="ep-select" id="av-{{ $item->id }}" name="availability">
                                                    @foreach(\App\Enums\ItemAvailability::cases() as $availability)
                                                        <option value="{{ $availability->value }}" @selected($item->availability === $availability)>
                                                            {{ $availability->label() }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div class="ep-field" style="flex:1;min-width:150px">
                                                <label class="ep-sr-only" for="sub-{{ $item->id }}">Substitut</label>
                                                <input class="ep-input" id="sub-{{ $item->id }}" name="substitute_name"
                                                       value="{{ $item->substitute_name }}" placeholder="Générique proposé…">
                                            </div>

                                            <div class="ep-field" style="width:82px">
                                                <label class="ep-sr-only" for="qty-{{ $item->id }}">Quantité</label>
                                                <input class="ep-input" id="qty-{{ $item->id }}" name="quantity" type="number"
                                                       min="1" max="99" value="{{ $item->quantity }}"
                                                       @disabled(! $composable)>
                                            </div>

                                            <button type="submit" class="ep-btn ep-btn--ghost ep-btn--md">Enregistrer</button>
                                        </form>

                                        @if($composable)
                                            <form method="POST" style="margin-top:.5rem"
                                                  action="{{ route('manager.verifications.items.destroy', [$order, $item]) }}"
                                                  onsubmit="return confirm('Retirer {{ $item->medicine_name }} de la commande ?')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="ep-btn ep-btn--danger-soft ep-btn--sm">Retirer la ligne</button>
                                            </form>
                                        @endif

                                        <p style="margin:.5rem 0 0">
                                            <span class="ep-badge" style="background:{{ $item->availability->tint() }};color:{{ $item->availability->color() }}">
                                                {{ $item->availability->label() }}
                                            </span>
                                            @if($item->pharmacy)
                                                <a href="tel:{{ preg_replace('/\s+/', '', $item->pharmacy->phone) }}" class="ep-mono ep-small" style="margin-left:.5rem">
                                                    Appeler {{ $item->pharmacy->name }}
                                                </a>
                                            @endif
                                        </p>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" data-label="">
                                        <span class="ep-small">
                                            Aucune ligne pour l'instant. Cette commande provient d'une ordonnance :
                                            lisez-la à droite, puis composez le panier ci-dessous.
                                        </span>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Composition du panier : seul chemin pour traiter une ordonnance,
                     qui arrive sans aucune ligne. --}}
                @if($composable)
                    <div style="padding:.875rem 1rem;border-top:1px solid var(--ep-rule)">
                        <p class="ep-small" style="margin:0 0 .5rem;font-weight:650">Ajouter un médicament à la commande</p>
                        <form method="POST" action="{{ route('manager.verifications.items.store', $order) }}"
                              class="ep-row" style="gap:.5rem;align-items:flex-end">
                            @csrf

                            <div class="ep-field" style="flex:1;min-width:220px">
                                <label class="ep-sr-only" for="add-medicine">Médicament</label>
                                <select class="ep-select" id="add-medicine" name="medicine_id" required>
                                    <option value="">Choisir un médicament du catalogue…</option>
                                    @foreach($medicines as $medicine)
                                        <option value="{{ $medicine->id }}" @selected(old('medicine_id') == $medicine->id)>
                                            {{ $medicine->name }}{{ $medicine->pack ? ' — ' . $medicine->pack : '' }}
                                            ({{ number_format($medicine->price, 0, ',', ' ') }} F){{ $medicine->requires_prescription ? ' · RX' : '' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="ep-field" style="width:96px">
                                <label class="ep-sr-only" for="add-quantity">Quantité</label>
                                <input class="ep-input" id="add-quantity" name="quantity" type="number"
                                       min="1" max="99" value="{{ old('quantity', 1) }}" required>
                            </div>

                            <button type="submit" class="ep-btn ep-btn--primary ep-btn--md">Ajouter</button>
                        </form>

                        <x-input-error :messages="$errors->get('medicine_id')" class="ep-error" />
                        <x-input-error :messages="$errors->get('quantity')" class="ep-error" />
                        <x-input-error :messages="$errors->get('items')" class="ep-error" />
                    </div>
                @endif

                @if($order->status === \App\Enums\OrderStatus::CHECKING)
                    <x-slot:footer>
                        <div class="ep-row">
                            <form method="POST" action="{{ route('manager.verifications.settle', $order) }}">
                                @csrf
                                <button type="submit" class="ep-btn ep-btn--primary">Valider la commande</button>
                            </form>
                            <form method="POST" action="{{ route('manager.verifications.refuse', $order) }}"
                                  class="ep-row" style="flex:1;min-width:260px">
                                @csrf
                                <input class="ep-input" name="reason" style="flex:1;min-width:160px"
                                       placeholder="Motif du refus…" required minlength="5" maxlength="255">
                                <button type="submit" class="ep-btn ep-btn--danger-soft">Refuser avec motif</button>
                            </form>
                        </div>
                    </x-slot:footer>
                @endif
            </x-ep.card>
        </div>

        <div class="ep-stack">
            @if($order->has_prescription)
                <x-ep.prescription-viewer
                    :order="$order"
                    :download-url="$order->prescription_path ? route('manager.orders.prescription', $order) : null" />
            @endif

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

            <x-ep.card title="Journal de la commande" flush>
                <div class="ep-feed" style="padding:.375rem 0">
                    @foreach($order->events as $event)
                        <article class="ep-feed__item">
                            <time class="ep-feed__time">{{ $event->created_at->format('H:i') }}</time>
                            <div style="min-width:0">
                                <p class="ep-feed__text">{{ $event->message }}</p>
                                <span class="ep-feed__tag" style="color:{{ $event->color }}">{{ $event->tag }}</span>
                            </div>
                        </article>
                    @endforeach
                </div>
            </x-ep.card>
        </div>
    </div>
@endsection
