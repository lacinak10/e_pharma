@extends('layouts.admin')

@section('title', 'Ordonnances reçues — ePharma')
@section('page_title', 'Ordonnances')
@section('page_subtitle', 'Ce que le client a envoyé, et ce qu’il souhaite exactement')

@section('content')
    <x-ep.card flush>
        <header class="ep-card__head">
            <h2 class="ep-card__title">Ordonnances reçues</h2>
            <div class="ep-row ep-spacer" style="gap:.25rem">
                @foreach(['' => 'Toutes', 'all' => 'Ordonnance complète', 'partial' => 'Partielle'] as $value => $label)
                    <a href="{{ route('manager.prescriptions.index', array_filter(['scope' => $value])) }}"
                       class="ep-btn ep-btn--sm {{ request('scope', '') === $value ? 'ep-btn--primary' : 'ep-btn--ghost' }}">{{ $label }}</a>
                @endforeach
            </div>
        </header>

        @if($orders->isEmpty())
            <x-ep.empty title="Aucune ordonnance reçue."
                        text="Les ordonnances téléversées par les clients apparaissent ici avec leur commentaire." />
        @else
            <div class="ep-table-wrap">
                <table class="ep-table ep-table--cards">
                    <thead>
                        <tr>
                            <th scope="col">Référence</th>
                            <th scope="col">Client</th>
                            <th scope="col">Périmètre demandé</th>
                            <th scope="col">Statut</th>
                            <th scope="col">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($orders as $order)
                            <tr>
                                <td data-label="Référence">
                                    <span class="ep-cell-ref">RX-{{ str_pad((string) $order->id, 4, '0', STR_PAD_LEFT) }}</span>
                                    <p class="ep-small" style="margin:0">{{ $order->reference }}</p>
                                </td>
                                <td data-label="Client">
                                    <p style="font-size:.84375rem;font-weight:650;margin:0">{{ $order->client?->short_name }}</p>
                                    <p class="ep-small" style="margin:0">{{ $order->created_at->locale('fr')->isoFormat('D MMM · HH:mm') }}</p>
                                </td>
                                <td data-label="Périmètre">
                                    <p style="font-size:.8125rem;margin:0;font-weight:650">
                                        {{ $order->prescription_scope === 'partial' ? 'Une partie seulement' : "Tous les médicaments" }}
                                    </p>
                                    @if($order->prescription_comment)
                                        <p class="ep-small" style="margin:.25rem 0 0">« {{ Str::limit($order->prescription_comment, 70) }} »</p>
                                    @endif
                                </td>
                                <td data-label="Statut"><x-ep.badge :status="$order->status" /></td>
                                <td data-label="Action">
                                    <div class="ep-cell-actions">
                                        <a href="{{ route('manager.verifications.show', $order) }}" class="ep-btn ep-btn--primary ep-btn--sm">Traiter</a>
                                        @if($order->prescription_path)
                                            <a href="{{ route('manager.orders.prescription', $order) }}" class="ep-btn ep-btn--ghost ep-btn--sm">Ordonnance</a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </x-ep.card>

    @if($orders->hasPages())
        {{ $orders->links() }}
    @endif
@endsection
