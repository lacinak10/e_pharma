@extends('layouts.admin')

@section('title', 'Suivi des livraisons — ePharma')
@section('page_title', 'Suivi des livraisons')
@section('page_subtitle', 'Où en est chaque course, étape par étape')

@section('content')
    <div class="ep-grid ep-grid--stats">
        @foreach($tabs as $tab)
            <x-ep.stat
                :label="$tab['status']->badge()"
                :value="$tab['count']"
                :color="$tab['status']->color()"
                :href="route('manager.deliveries.index', ['status' => $tab['status']->value])" />
        @endforeach
    </div>

    <x-ep.card flush>
        <header class="ep-card__head">
            <h2 class="ep-card__title">
                {{ $status ? (\App\Enums\OrderStatus::tryFrom($status)?->badge() ?? 'Courses') : 'Courses en cours' }}
            </h2>
            @if($status)
                <a href="{{ route('manager.deliveries.index') }}" class="ep-spacer ep-btn ep-btn--ghost ep-btn--sm">
                    Toutes les courses en cours
                </a>
            @endif
        </header>

        @if($orders->isEmpty())
            <x-ep.empty title="Aucune course ici."
                        text="Les commandes attribuées à un livreur apparaissent dans ce suivi." />
        @else
            <div class="ep-table-wrap">
                <table class="ep-table ep-table--cards">
                    <thead>
                        <tr>
                            <th scope="col">Référence</th>
                            <th scope="col">Livreur</th>
                            <th scope="col">Retrait</th>
                            <th scope="col">Client</th>
                            <th scope="col">Étape</th>
                            <th scope="col">ETA</th>
                            <th scope="col">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($orders as $order)
                            <tr>
                                <td data-label="Référence"><span class="ep-cell-ref">{{ $order->reference }}</span></td>
                                <td data-label="Livreur" class="ep-cell-name">
                                    @if($order->assignment?->courier)
                                        <p style="font-size:.84375rem;font-weight:650;margin:0">{{ $order->assignment->courier->short_name }}</p>
                                        <a href="tel:{{ preg_replace('/\s+/', '', $order->assignment->courier->phone ?? '') }}" class="ep-mono ep-small">
                                            {{ $order->assignment->courier->phone }}
                                        </a>
                                    @else
                                        <span class="ep-small">Non attribué</span>
                                    @endif
                                </td>
                                <td data-label="Retrait"><span class="ep-small">{{ $order->pharmacy?->name ?? '—' }}</span></td>
                                <td data-label="Client" class="ep-cell-name">
                                    <p style="font-size:.84375rem;margin:0">{{ $order->client?->short_name }}</p>
                                    <p class="ep-small" style="margin:0">{{ Str::limit($order->delivery_address, 20) }}</p>
                                </td>
                                <td data-label="Étape"><x-ep.badge :status="$order->status" /></td>
                                <td data-label="ETA">
                                    <span class="ep-cell-num">{{ $order->eta_minutes ? $order->eta_minutes . ' min' : '—' }}</span>
                                </td>
                                <td data-label="Action">
                                    <a href="{{ route('manager.orders.show', $order) }}" class="ep-btn ep-btn--ghost ep-btn--sm">Détail</a>
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
