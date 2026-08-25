@extends('layouts.admin')

@section('title', 'Clients — ePharma')
@section('page_title', 'Clients')
@section('page_subtitle', $customers->total() . ' client(s) inscrits')

@section('content')
    <x-ep.card flush>
        <header class="ep-card__head">
            <h2 class="ep-card__title">Tous les clients</h2>
            <form method="GET" class="ep-row ep-spacer" style="gap:.375rem">
                <label class="ep-sr-only" for="q">Nom</label>
                <input class="ep-input" id="q" name="q" value="{{ $q }}" placeholder="Nom du client…"
                       style="width:180px;padding:.5rem .75rem;font-size:.8125rem">
                <button type="submit" class="ep-btn ep-btn--ghost ep-btn--sm">Filtrer</button>
            </form>
        </header>

        @if($customers->isEmpty())
            <x-ep.empty title="Aucun client." text="Les comptes clients apparaissent ici dès la première inscription." />
        @else
            <div class="ep-table-wrap">
                <table class="ep-table ep-table--cards">
                    <thead>
                        <tr>
                            <th scope="col" colspan="2">Client</th>
                            <th scope="col">Contact</th>
                            <th scope="col">Quartier</th>
                            <th scope="col">Commandes</th>
                            <th scope="col">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($customers as $customer)
                            <tr>
                                <td data-label="" style="width:52px">
                                    <img src="{{ $customer->avatar_url }}" alt=""
                                         style="width:36px;height:36px;border-radius:50%;object-fit:cover">
                                </td>
                                <td data-label="Client" class="ep-cell-name">
                                    <p style="font-size:.84375rem;font-weight:650;margin:0">{{ $customer->name }}</p>
                                    <p class="ep-small" style="margin:0">
                                        Inscrit {{ $customer->created_at->locale('fr')->isoFormat('D MMM YYYY') }}
                                    </p>
                                </td>
                                <td data-label="Contact">
                                    <p class="ep-small" style="margin:0">{{ $customer->email }}</p>
                                    @if($customer->phone)
                                        <a href="tel:{{ preg_replace('/\s+/', '', $customer->phone) }}" class="ep-mono ep-small">{{ $customer->phone }}</a>
                                    @endif
                                </td>
                                <td data-label="Quartier"><span class="ep-small">{{ $customer->zone ?? '—' }}</span></td>
                                <td data-label="Commandes"><span class="ep-cell-num">{{ $customer->orders_count }}</span></td>
                                <td data-label="Action">
                                    <a href="{{ route('manager.customers.show', $customer) }}" class="ep-btn ep-btn--ghost ep-btn--sm">Fiche</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </x-ep.card>

    @if($customers->hasPages()) {{ $customers->links() }} @endif
@endsection
