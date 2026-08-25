@extends('layouts.admin')

@section('title', 'Mes courses — ePharma')
@section('page_title', 'Mes courses')
@section('page_subtitle', 'Ce qui vous attend, et ce que vous avez déjà livré')

@section('content')
    <div class="ep-grid ep-grid--stats">
        <x-ep.stat label="Nouvelles courses" :value="$counts['new']" color="#B87514"
                   :href="route('courier.my_orders.index', ['filter' => 'new'])" />
        <x-ep.stat label="En cours" :value="$counts['active']" color="#33557F"
                   :href="route('courier.my_orders.index', ['filter' => 'active'])" />
        <x-ep.stat label="Terminées" :value="$counts['done']" color="#0E5C43"
                   :href="route('courier.my_orders.index', ['filter' => 'done'])" />
    </div>

    <x-ep.card flush>
        <header class="ep-card__head">
            <h2 class="ep-card__title">
                {{ ['new' => 'Nouvelles courses', 'active' => 'Courses en cours', 'done' => 'Historique'][$filter] ?? 'Toutes mes courses' }}
            </h2>
            <div class="ep-row ep-spacer" style="gap:.25rem">
                @foreach(['all' => 'Toutes', 'new' => 'Nouvelles', 'active' => 'En cours', 'done' => 'Terminées'] as $value => $label)
                    <a href="{{ route('courier.my_orders.index', $value === 'all' ? [] : ['filter' => $value]) }}"
                       class="ep-btn ep-btn--sm {{ $filter === $value ? 'ep-btn--primary' : 'ep-btn--ghost' }}">{{ $label }}</a>
                @endforeach
            </div>
        </header>

        @if($orders->isEmpty())
            <x-ep.empty title="Aucune course ici."
                        text="Les courses que le manager vous attribue apparaissent dans cette liste." />
        @else
            <div class="ep-table-wrap">
                <table class="ep-table ep-table--cards">
                    <thead>
                        <tr>
                            <th scope="col">Référence</th>
                            <th scope="col">Retrait</th>
                            <th scope="col">Client</th>
                            <th scope="col">Étape</th>
                            <th scope="col">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($orders as $order)
                            <tr>
                                <td data-label="Référence">
                                    <span class="ep-cell-ref">{{ $order->reference }}</span>
                                    @if($order->has_prescription)<span class="ep-badge ep-badge--rx">RX</span>@endif
                                </td>
                                <td data-label="Retrait">
                                    <p style="font-size:.84375rem;margin:0">{{ $order->pharmacy?->name ?? 'Pharmacie à confirmer' }}</p>
                                    @if($order->pharmacy)
                                        <a href="tel:{{ preg_replace('/\s+/', '', $order->pharmacy->phone) }}" class="ep-mono ep-small">
                                            {{ $order->pharmacy->phone }}
                                        </a>
                                    @endif
                                </td>
                                <td data-label="Client">
                                    <p style="font-size:.84375rem;font-weight:650;margin:0">{{ $order->client?->short_name }}</p>
                                    <p class="ep-small" style="margin:0">{{ Str::limit($order->delivery_address, 28) }}</p>
                                </td>
                                <td data-label="Étape"><x-ep.badge :status="$order->status" /></td>
                                <td data-label="Action">
                                    <div class="ep-cell-actions">
                                        @if($order->status->courierActionLabel())
                                            <form method="POST" action="{{ route($order->status === \App\Enums\OrderStatus::COURIER_ASSIGNED ? 'courier.my_orders.accept' : 'courier.my_orders.advance', $order) }}">
                                                @csrf @method('PATCH')
                                                <button type="submit" class="ep-btn ep-btn--primary ep-btn--sm">
                                                    {{ $order->status->courierActionLabel() }}
                                                </button>
                                            </form>
                                        @endif
                                        <a href="{{ route('courier.my_orders.show', $order) }}" class="ep-btn ep-btn--ghost ep-btn--sm">Détail</a>
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
