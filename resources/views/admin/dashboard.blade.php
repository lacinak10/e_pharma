@extends('layouts.admin')

@section('title', 'Tableau de bord — ePharma')
@section('page_title', 'Tableau de bord')

@section('alertbar')
    @if($alerts->isNotEmpty())
        <div class="ep-alertbar">
            <span class="ep-alertbar__dot" aria-hidden="true"></span>
            <strong style="font-size:.875rem">
                Alerte 5 minutes — {{ $alerts->count() }} commande{{ $alerts->count() > 1 ? 's' : '' }} proche{{ $alerts->count() > 1 ? 's' : '' }} de l'échéance
            </strong>
            <div class="ep-row" style="flex:1;min-width:0">
                @foreach($alerts->take(3) as $alert)
                    <div class="ep-alertbar__item"
                         data-ep-chrono data-deadline="{{ $alert->check_deadline_at?->toIso8601String() }}">
                        <span class="ep-mono" style="font-size:.9375rem;font-weight:600" data-ep-chrono-value>{{ $alert->check_clock }}</span>
                        <span style="font-size:.78125rem">
                            {{ $alert->reference }} · {{ $alert->client?->short_name }} ·
                            {{ $alert->has_prescription ? 'ordonnance' : $alert->items->count() . ' articles' }}
                        </span>
                        <a href="{{ route('manager.verifications.show', $alert) }}">Traiter</a>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
@endsection

@section('content')

    {{-- ── Compteurs ─────────────────────────────────────────────── --}}
    <div class="ep-grid ep-grid--stats">
        @foreach($counters as $counter)
            <x-ep.stat
                :label="$counter['label']"
                :value="$counter['value']"
                :delta="$counter['delta']"
                :color="$counter['color']"
                :href="$counter['href']" />
        @endforeach
    </div>

    <div class="ep-split">

        {{-- ══ Colonne principale ═══════════════════════════════════ --}}
        <div class="ep-stack">

            {{-- Vérification de disponibilité --}}
            <x-ep.card flush>
                <header class="ep-card__head">
                    <h2 class="ep-card__title">Vérification de disponibilité</h2>
                    @if($focus)
                        <span class="ep-mono ep-small">
                            {{ $focus->reference }} · {{ $focus->client?->short_name }} · {{ $focus->delivery_address }}
                        </span>
                        <span class="ep-spacer ep-chrono-inline @if($focus->secondsLeftForCheck() <= 60) ep-chrono-inline--urgent @endif"
                              data-ep-chrono data-urgent="true" data-ep-chrono-reload
                              data-deadline="{{ $focus->check_deadline_at?->toIso8601String() }}">
                            <span class="ep-eyebrow">Chrono</span>
                            <span class="ep-chrono-inline__value" data-ep-chrono-value>{{ $focus->check_clock }}</span>
                        </span>
                    @endif
                </header>

                @if(! $focus)
                    <x-ep.empty
                        title="Aucune vérification en cours."
                        text="Dès qu'une commande est validée, ses médicaments apparaissent ici pour être confirmés auprès des pharmacies partenaires.">
                        <x-slot:actions>
                            <a href="{{ route('manager.verifications.index') }}" class="ep-btn ep-btn--ghost ep-btn--md">
                                Voir les commandes à valider
                            </a>
                        </x-slot:actions>
                    </x-ep.empty>
                @else
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
                                @forelse($focus->items as $item)
                                    <tr>
                                        <td data-label="Médicament">
                                            <div style="min-width:0">
                                                <p style="font-size:.875rem;font-weight:650;display:flex;align-items:center;gap:.5rem;margin:0">
                                                    {{ $item->medicine_name }}
                                                    @if($item->requires_prescription)
                                                        <span class="ep-badge ep-badge--rx">RX</span>
                                                    @endif
                                                </p>
                                                <p class="ep-mono ep-small" style="margin:3px 0 0">{{ $item->pack }}</p>
                                            </div>
                                        </td>
                                        <td data-label="Pharmacie">
                                            @if($item->pharmacy)
                                                <p style="font-size:.84375rem;margin:0">{{ $item->pharmacy->name }}</p>
                                                <a href="tel:{{ preg_replace('/\s+/', '', $item->pharmacy->phone) }}" class="ep-mono ep-small">
                                                    {{ $item->pharmacy->phone }}
                                                </a>
                                            @else
                                                <span class="ep-small">Aucune pharmacie contactée</span>
                                            @endif
                                        </td>
                                        <td data-label="Verdict">
                                            <div class="ep-cell-actions">
                                                @foreach([
                                                    ['available', 'Dispo.', 'ep-btn--primary'],
                                                    ['unavailable', 'Indispo.', 'ep-btn--danger-soft'],
                                                ] as [$value, $label, $variant])
                                                    <form method="POST" action="{{ route('manager.verifications.item', [$focus, $item]) }}">
                                                        @csrf @method('PATCH')
                                                        <input type="hidden" name="availability" value="{{ $value }}">
                                                        <input type="hidden" name="pharmacy_id" value="{{ $item->pharmacy_id }}">
                                                        <button type="submit"
                                                                class="ep-btn ep-btn--sm {{ $item->availability->value === $value ? $variant : 'ep-btn--ghost' }}">
                                                            {{ $label }}
                                                        </button>
                                                    </form>
                                                @endforeach
                                                <a href="{{ route('manager.verifications.show', $focus) }}" class="ep-btn ep-btn--ghost ep-btn--sm">Substitut</a>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" data-label="">
                                            <span class="ep-small">Commande sur ordonnance : composez le panier depuis la fiche de vérification.</span>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <footer class="ep-card__foot">
                        <div class="ep-grid" style="grid-template-columns:minmax(0,1fr);gap:1.25rem">
                            <div class="ep-split" style="gap:1.25rem">
                                @if($focus->has_prescription)
                                    <div class="ep-row ep-row--nowrap" style="gap:.875rem;align-items:flex-start">
                                        <img src="{{ $focus->prescription_path ? route('manager.orders.prescription', $focus) : asset('assets/images/photos/ordonnance.jpg') }}"
                                             alt="Ordonnance du client"
                                             style="width:96px;height:76px;object-fit:cover;border-radius:7px;border:1px solid var(--ep-rule);flex:none">
                                        <div style="min-width:0">
                                            <p class="ep-eyebrow">
                                                Ordonnance RX-{{ str_pad((string) $focus->id, 4, '0', STR_PAD_LEFT) }} · commentaire du client
                                            </p>
                                            @if($focus->prescription_comment)
                                                <p class="ep-quote" style="margin-top:.5rem">« {{ $focus->prescription_comment }} »</p>
                                            @else
                                                <p class="ep-small" style="margin-top:.5rem">
                                                    {{ $focus->prescription_scope === 'partial' ? 'Une partie seulement des médicaments.' : "Tous les médicaments de l'ordonnance." }}
                                                </p>
                                            @endif
                                        </div>
                                    </div>
                                @else
                                    <p class="ep-small" style="margin:0">
                                        Commande sans ordonnance · {{ $focus->items->count() }} article(s) ·
                                        <span class="ep-mono">{{ number_format($focus->total_amount, 0, ',', ' ') }} F</span>
                                    </p>
                                @endif

                                <div class="ep-stack" style="gap:.5rem">
                                    <form method="POST" action="{{ route('manager.verifications.settle', $focus) }}">
                                        @csrf
                                        <button type="submit" class="ep-btn ep-btn--primary ep-btn--block">Valider la commande</button>
                                    </form>
                                    <a href="{{ route('manager.verifications.show', $focus) }}" class="ep-btn ep-btn--danger-soft ep-btn--block">
                                        Refuser avec motif
                                    </a>
                                </div>
                            </div>
                        </div>
                    </footer>
                @endif
            </x-ep.card>

            {{-- File des commandes --}}
            <x-ep.card flush>
                <header class="ep-card__head">
                    <h2 class="ep-card__title">File des commandes</h2>
                    <div class="ep-row" style="gap:.25rem">
                        @foreach($queueTabs as $tab)
                            <a href="{{ $tab['url'] }}"
                               style="font-size:.78125rem;font-weight:{{ $tab['active'] ? 650 : 600 }};padding:.375rem .6875rem;border-radius:7px;
                                      {{ $tab['active'] ? 'background:var(--ep-ink);color:#fff' : 'color:var(--ep-text-3)' }}">
                                {{ $tab['label'] }} · {{ $tab['count'] }}
                            </a>
                        @endforeach
                    </div>
                    <a href="{{ route('manager.orders.index') }}" class="ep-spacer" style="font-size:.8125rem;font-weight:650">Tout voir →</a>
                </header>

                <div class="ep-table-wrap">
                    <table class="ep-table ep-table--cards">
                        <thead>
                            <tr>
                                <th scope="col">Référence</th>
                                <th scope="col">Client</th>
                                <th scope="col">Articles</th>
                                <th scope="col">Statut</th>
                                <th scope="col">Montant</th>
                                <th scope="col">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($queue as $order)
                                <tr>
                                    <td data-label="Référence"><span class="ep-cell-ref">{{ $order->reference }}</span></td>
                                    <td data-label="Client" class="ep-cell-name">
                                        <div style="min-width:0">
                                            <p style="font-size:.84375rem;font-weight:650;margin:0">{{ $order->client?->short_name ?? 'Client' }}</p>
                                            <p class="ep-small" style="margin:0">{{ Str::limit($order->delivery_address, 22) }}</p>
                                        </div>
                                    </td>
                                    <td data-label="Articles">
                                        <span class="ep-small" style="color:var(--ep-text-3)">
                                            {{ $order->has_prescription ? 'Ordonnance · ' : '' }}{{ Str::limit($order->items_summary, 34) }}
                                        </span>
                                    </td>
                                    <td data-label="Statut"><x-ep.badge :status="$order->status" /></td>
                                    <td data-label="Montant"><span class="ep-cell-num">{{ number_format($order->total_amount, 0, ',', ' ') }} F</span></td>
                                    <td data-label="Action">
                                        <div class="ep-cell-actions">
                                            @if($order->status->needsManager())
                                                <a href="{{ route('manager.verifications.show', $order) }}" class="ep-btn ep-btn--primary ep-btn--sm">Traiter</a>
                                            @endif
                                            <a href="{{ route('manager.orders.show', $order) }}" class="ep-btn ep-btn--ghost ep-btn--sm">Détail</a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" data-label="">
                                        <span class="ep-small">Aucune commande pour le moment.</span>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-ep.card>

            {{-- Attribution du livreur --}}
            <x-ep.card flush>
                <header class="ep-card__head">
                    <h2 class="ep-card__title">Attribution du livreur</h2>
                    @if($assignable)
                        <span class="ep-mono ep-small">
                            {{ $assignable->reference }} · départ {{ $assignable->pharmacy?->name ?? 'pharmacie à confirmer' }}
                        </span>
                    @endif
                    <span class="ep-spacer ep-badge ep-badge--green">Suggestion automatique</span>
                </header>

                @if(! $assignable)
                    <x-ep.empty
                        title="Aucune commande à attribuer."
                        text="Les commandes validées apparaissent ici pour être confiées au livreur le plus proche." />
                @else
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
                                @forelse($couriers as $index => $courier)
                                    <tr @if($index === 0) style="background:#F6FBF8" @endif>
                                        <td data-label="" style="width:44px">
                                            <img src="{{ $courier->avatar_url }}" alt=""
                                                 style="width:36px;height:36px;border-radius:50%;object-fit:cover">
                                        </td>
                                        <td data-label="Livreur" class="ep-cell-name">
                                            <div style="min-width:0">
                                                <p style="font-size:.84375rem;font-weight:650;display:flex;align-items:center;gap:.5rem;margin:0">
                                                    {{ $courier->short_name }}
                                                    @if($index === 0)<span class="ep-badge ep-badge--green">Suggéré</span>@endif
                                                </p>
                                                <p class="ep-mono ep-small" style="margin:0">{{ $courier->phone ?? '—' }}</p>
                                            </div>
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
                                                    {{ $courier->load }} course{{ $courier->load > 1 ? 's' : '' }}
                                                </span>
                                            </span>
                                        </td>
                                        <td data-label="Action">
                                            <form method="POST" action="{{ route('manager.assignments.store') }}">
                                                @csrf
                                                <input type="hidden" name="order_id" value="{{ $assignable->id }}">
                                                <input type="hidden" name="courier_id" value="{{ $courier->id }}">
                                                <button type="submit"
                                                        class="ep-btn ep-btn--sm {{ $index === 0 ? 'ep-btn--primary' : 'ep-btn--ghost' }}"
                                                        @disabled($courier->state === 'Indisponible')>
                                                    {{ $courier->state === 'Indisponible' ? 'Indispo.' : 'Attribuer' }}
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" data-label=""><span class="ep-small">Aucun livreur actif.</span></td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                @endif
            </x-ep.card>
        </div>

        {{-- ══ Rail latéral ═════════════════════════════════════════ --}}
        <div class="ep-stack">

            {{-- Chiffres clés --}}
            <div class="ep-card ep-card--dark" style="padding:1.125rem">
                <div class="ep-row ep-row--nowrap" style="margin-bottom:1rem">
                    <span class="ep-eyebrow" style="color:#7E8F87">Chiffres clés · ce soir</span>
                    <span class="ep-spacer" style="width:6px;height:6px;border-radius:50%;background:var(--ep-green-soft);animation:epPulse 1.6s ease-in-out infinite"></span>
                </div>
                <div class="ep-grid" style="grid-template-columns:repeat(auto-fit,minmax(min(100%,140px),1fr));gap:.875rem">
                    @foreach($kpis as $kpi)
                        <div class="ep-kpi">
                            <p class="ep-kpi__label">{{ $kpi['label'] }}</p>
                            <p style="margin:.5rem 0 0"><span class="ep-kpi__value">{{ $kpi['value'] }}</span></p>
                            <div class="ep-kpi__track">
                                <span class="ep-kpi__fill" style="width:{{ $kpi['bar'] }};background:{{ $kpi['color'] }}"></span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Livreurs en course --}}
            <x-ep.card flush>
                <header class="ep-card__head">
                    <h2 class="ep-card__title">Livreurs en course</h2>
                    <span class="ep-spacer ep-mono ep-small">{{ $onTheRoad->count() }} actif{{ $onTheRoad->count() > 1 ? 's' : '' }}</span>
                </header>
                <img src="{{ asset('assets/images/photos/carte-abidjan.svg') }}"
                     alt="Carte d'Abidjan avec la position des livreurs en course"
                     style="width:100%;height:180px;object-fit:cover;display:block">
                <div style="padding:.75rem 1rem;display:flex;flex-direction:column;gap:.625rem">
                    @forelse($onTheRoad as $row)
                        <div class="ep-row ep-row--nowrap" style="font-size:.8125rem">
                            <span style="width:7px;height:7px;border-radius:50%;background:{{ $row['status']->color() }};flex:none"></span>
                            <span style="flex:1;min-width:0">{{ $row['courier'] }} · {{ $row['label'] }}</span>
                            <span class="ep-mono ep-small">{{ $row['eta'] }}</span>
                        </div>
                    @empty
                        <p class="ep-small" style="margin:0">Aucun livreur sur la route en ce moment.</p>
                    @endforelse
                </div>
            </x-ep.card>

            {{-- Activité en direct --}}
            <x-ep.card title="Activité en direct" flush>
                <div class="ep-feed" style="padding:.375rem 0" data-ep-live-refresh="45">
                    @forelse($feed as $event)
                        <article class="ep-feed__item">
                            <time class="ep-feed__time" datetime="{{ $event->created_at->toIso8601String() }}">
                                {{ $event->created_at->format('H:i') }}
                            </time>
                            <div style="min-width:0">
                                <p class="ep-feed__text">{{ $event->message }}</p>
                                <span class="ep-feed__tag" style="color:{{ $event->color }}">{{ $event->tag }}</span>
                            </div>
                        </article>
                    @empty
                        <p class="ep-small" style="padding:.75rem 1rem;margin:0">Rien à signaler pour l'instant.</p>
                    @endforelse
                </div>
            </x-ep.card>

            {{-- Fiabilité des partenaires --}}
            <x-ep.card>
                <div class="ep-row ep-row--nowrap" style="margin-bottom:.875rem">
                    <h2 class="ep-card__title">Fiabilité des partenaires</h2>
                    <a href="{{ route('manager.pharmacies.index') }}" class="ep-spacer" style="font-size:.78125rem;font-weight:650">Module →</a>
                </div>
                @forelse($partners as $pharmacy)
                    <div class="ep-row ep-row--nowrap" style="gap:.75rem;padding:.5rem 0">
                        <span style="font-size:.8125rem;flex:1;min-width:0">{{ $pharmacy->name }}</span>
                        <span class="ep-meter">
                            <span style="width:{{ $pharmacy->reliability }}%;background:{{ $pharmacy->reliability_color }}"></span>
                        </span>
                        <span class="ep-mono ep-small" style="width:38px;text-align:right">{{ $pharmacy->reliability }} %</span>
                    </div>
                @empty
                    <p class="ep-small" style="margin:0">
                        Aucune pharmacie partenaire enregistrée.
                        <a href="{{ route('manager.pharmacies.create') }}">En ajouter une</a>.
                    </p>
                @endforelse
            </x-ep.card>
        </div>
    </div>
@endsection
