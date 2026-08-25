@extends('layouts.admin')

@section('title', 'Vérification de disponibilité — ePharma')
@section('page_title', 'Vérification de disponibilité')
@section('page_subtitle', 'Ce qui attend une décision, et ce qui court contre le chrono')

@section('content')

    {{-- Étape 1 : commandes à prendre en charge --}}
    <x-ep.card flush>
        <header class="ep-card__head">
            <h2 class="ep-card__title">En attente de validation</h2>
            <span class="ep-spacer ep-badge ep-badge--amber">{{ $awaiting->count() }}</span>
        </header>

        @if($awaiting->isEmpty())
            <x-ep.empty title="Rien à valider."
                        text="Les nouvelles commandes des clients arrivent ici avant toute vérification." />
        @else
            <div class="ep-table-wrap">
                <table class="ep-table ep-table--cards">
                    <thead>
                        <tr>
                            <th scope="col">Référence</th>
                            <th scope="col">Client</th>
                            <th scope="col">Contenu</th>
                            <th scope="col">Reçue</th>
                            <th scope="col">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($awaiting as $order)
                            <tr>
                                <td data-label="Référence"><span class="ep-cell-ref">{{ $order->reference }}</span></td>
                                <td data-label="Client">
                                    <p style="font-size:.84375rem;font-weight:650;margin:0">{{ $order->client?->short_name }}</p>
                                    <p class="ep-small" style="margin:0">{{ $order->delivery_address }}</p>
                                </td>
                                <td data-label="Contenu">
                                    <span class="ep-small">
                                        @if($order->has_prescription)
                                            <span class="ep-badge ep-badge--rx">Ordonnance</span>
                                            {{ $order->prescription_scope === 'partial' ? 'partielle' : 'complète' }}
                                        @else
                                            {{ $order->items->count() }} article(s)
                                        @endif
                                    </span>
                                </td>
                                <td data-label="Reçue"><span class="ep-cell-num">{{ $order->created_at->diffForHumans(short: true) }}</span></td>
                                <td data-label="Action">
                                    <div class="ep-cell-actions">
                                        <form method="POST" action="{{ route('manager.verifications.start', $order) }}">
                                            @csrf
                                            <button type="submit" class="ep-btn ep-btn--primary ep-btn--sm">Lancer la vérification</button>
                                        </form>
                                        <a href="{{ route('manager.verifications.show', $order) }}" class="ep-btn ep-btn--ghost ep-btn--sm">Ouvrir</a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </x-ep.card>

    {{-- Étape 3 : vérifications en cours, chrono vivant --}}
    <x-ep.card flush>
        <header class="ep-card__head">
            <h2 class="ep-card__title">Vérifications en cours</h2>
            <span class="ep-spacer ep-badge ep-badge--red">{{ $checking->count() }}</span>
        </header>

        @if($checking->isEmpty())
            <x-ep.empty title="Aucune vérification en cours."
                        text="Lancez une vérification depuis la liste ci-dessus pour démarrer le chrono de 5 minutes." />
        @else
            <div class="ep-table-wrap">
                <table class="ep-table ep-table--cards">
                    <thead>
                        <tr>
                            <th scope="col">Chrono</th>
                            <th scope="col">Référence</th>
                            <th scope="col">Client</th>
                            <th scope="col">Lignes vérifiées</th>
                            <th scope="col">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($checking as $order)
                            @php
                                $settled = $order->items->filter(fn($i) => $i->availability->isSettled())->count();
                                $urgent  = $order->secondsLeftForCheck() <= 60;
                            @endphp
                            <tr>
                                <td data-label="Chrono">
                                    <span class="ep-chrono-inline @if($urgent) ep-chrono-inline--urgent @endif"
                                          data-ep-chrono data-urgent="true"
                                          data-deadline="{{ $order->check_deadline_at?->toIso8601String() }}">
                                        <span class="ep-chrono-inline__value" data-ep-chrono-value>{{ $order->check_clock }}</span>
                                    </span>
                                </td>
                                <td data-label="Référence"><span class="ep-cell-ref">{{ $order->reference }}</span></td>
                                <td data-label="Client">
                                    <span style="font-size:.84375rem;font-weight:650">{{ $order->client?->short_name }}</span>
                                </td>
                                <td data-label="Lignes vérifiées">
                                    <span class="ep-cell-num">{{ $settled }} / {{ $order->items->count() }}</span>
                                </td>
                                <td data-label="Action">
                                    <a href="{{ route('manager.verifications.show', $order) }}" class="ep-btn ep-btn--primary ep-btn--sm">Traiter</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </x-ep.card>
@endsection
