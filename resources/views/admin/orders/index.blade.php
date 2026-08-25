@extends('layouts.admin')

@section('title', 'Commandes — ePharma')
@section('page_title', 'Commandes')
@section('page_subtitle', $orders->total() . ' commande(s) · toutes les étapes du cycle de vie')

@section('content')
    <x-ep.card flush>
        <header class="ep-card__head">
            <h2 class="ep-card__title">Toutes les commandes</h2>

            <form method="GET" class="ep-row ep-spacer" style="gap:.375rem">
                <label class="ep-sr-only" for="q">Client</label>
                <input class="ep-input" id="q" name="q" value="{{ $q }}" placeholder="Nom du client…"
                       style="width:180px;padding:.5rem .75rem;font-size:.8125rem">

                <label class="ep-sr-only" for="status">Statut</label>
                <select class="ep-select" id="status" name="status" style="width:auto;padding:.5rem .75rem;font-size:.8125rem">
                    <option value="">Tous les statuts</option>
                    @foreach(\App\Enums\OrderStatus::lifecycle() as $case)
                        <option value="{{ $case->value }}" @selected($status === $case->value)>
                            {{ $case->number() }}. {{ $case->badge() }}
                        </option>
                    @endforeach
                </select>

                <label class="ep-chip">
                    <input type="checkbox" name="with_prescription" value="1" @checked($withPrescription)>
                    Ordonnance
                </label>

                <button type="submit" class="ep-btn ep-btn--ghost ep-btn--sm">Filtrer</button>
                @if($q || $status || $withPrescription)
                    <a href="{{ route('manager.orders.index') }}" class="ep-btn ep-btn--ghost ep-btn--sm">Réinitialiser</a>
                @endif
            </form>
        </header>

        @if($orders->isEmpty())
            <x-ep.empty title="Aucune commande ne correspond."
                        text="Ajustez les filtres, ou attendez la prochaine commande client." />
        @else
            <div class="ep-table-wrap">
                <table class="ep-table ep-table--cards">
                    <thead>
                        <tr>
                            <th scope="col">Référence</th>
                            <th scope="col">Client</th>
                            <th scope="col">Contenu</th>
                            <th scope="col">Statut</th>
                            <th scope="col">Livreur</th>
                            <th scope="col">Montant</th>
                            <th scope="col">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($orders as $order)
                            <tr>
                                <td data-label="Référence">
                                    <span class="ep-cell-ref">{{ $order->reference }}</span>
                                    <p class="ep-small" style="margin:0">{{ $order->created_at->locale('fr')->isoFormat('D MMM · HH:mm') }}</p>
                                </td>
                                <td data-label="Client" class="ep-cell-name">
                                    <p style="font-size:.84375rem;font-weight:650;margin:0">{{ $order->client?->short_name ?? 'Client' }}</p>
                                    <p class="ep-small" style="margin:0">{{ Str::limit($order->delivery_address, 22) }}</p>
                                </td>
                                <td data-label="Contenu">
                                    <span class="ep-small" style="color:var(--ep-text-3)">
                                        @if($order->has_prescription)<span class="ep-badge ep-badge--rx">Ordonnance</span> @endif
                                        {{ Str::limit($order->items_summary, 30) }}
                                    </span>
                                </td>
                                <td data-label="Statut"><x-ep.badge :status="$order->status" /></td>
                                <td data-label="Livreur">
                                    <span class="ep-small">{{ $order->assignment?->courier?->short_name ?? '—' }}</span>
                                </td>
                                <td data-label="Montant">
                                    <span class="ep-cell-num">{{ number_format($order->total_amount, 0, ',', ' ') }} F</span>
                                </td>
                                <td data-label="Action">
                                    <div class="ep-cell-actions">
                                        @if($order->status->needsManager())
                                            <a href="{{ route('manager.verifications.show', $order) }}" class="ep-btn ep-btn--primary ep-btn--sm">Traiter</a>
                                        @endif
                                        <a href="{{ route('manager.orders.show', $order) }}" class="ep-btn ep-btn--ghost ep-btn--sm">Détail</a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </x-ep.card>

    @if($orders->hasPages()) {{ $orders->links() }} @endif
@endsection
