@extends('layouts.admin')

@section('title', $user->name . ' — ePharma')
@section('page_title', $user->name)
@section('page_subtitle', 'Client depuis le ' . $user->created_at->locale('fr')->isoFormat('D MMMM YYYY'))

@section('content')
    <div class="ep-split">
        <x-ep.card title="Historique des commandes" flush>
            @if($orders->isEmpty())
                <x-ep.empty title="Aucune commande." text="Ce client n'a pas encore commandé." />
            @else
                <div class="ep-table-wrap">
                    <table class="ep-table ep-table--cards">
                        <thead>
                            <tr>
                                <th scope="col">Référence</th>
                                <th scope="col">Date</th>
                                <th scope="col">Statut</th>
                                <th scope="col">Montant</th>
                                <th scope="col">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($orders as $order)
                                <tr>
                                    <td data-label="Référence"><span class="ep-cell-ref">{{ $order->reference }}</span></td>
                                    <td data-label="Date">
                                        <span class="ep-small">{{ $order->created_at->locale('fr')->isoFormat('D MMM YYYY · HH:mm') }}</span>
                                    </td>
                                    <td data-label="Statut"><x-ep.badge :status="$order->status" /></td>
                                    <td data-label="Montant"><span class="ep-cell-num">{{ number_format($order->total_amount, 0, ',', ' ') }} F</span></td>
                                    <td data-label="Action">
                                        <a href="{{ route('manager.orders.show', $order) }}" class="ep-btn ep-btn--ghost ep-btn--sm">Détail</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            @if($orders->hasPages())
                <x-slot:footer>{{ $orders->links() }}</x-slot:footer>
            @endif
        </x-ep.card>

        <div class="ep-stack">
            <x-ep.card title="Coordonnées">
                <div class="ep-row ep-row--nowrap" style="gap:.75rem;margin-bottom:1rem">
                    <img src="{{ $user->avatar_url }}" alt=""
                         style="width:48px;height:48px;border-radius:50%;object-fit:cover;flex:none">
                    <div style="min-width:0">
                        <p style="font-size:.9375rem;font-weight:650;margin:0">{{ $user->name }}</p>
                        <p class="ep-small" style="margin:0">{{ $user->zone ?? 'Quartier non renseigné' }}</p>
                    </div>
                </div>

                <p class="ep-small" style="margin:0">{{ $user->email }}</p>
                @if($user->phone)
                    <a href="tel:{{ preg_replace('/\s+/', '', $user->phone) }}" class="ep-mono ep-small">{{ $user->phone }}</a>
                @endif
            </x-ep.card>

            <x-ep.card title="En un coup d'œil">
                <div class="ep-row ep-row--nowrap" style="justify-content:space-between">
                    <span class="ep-small">Commandes</span>
                    <span class="ep-mono ep-small">{{ $orders->total() }}</span>
                </div>
                <div class="ep-row ep-row--nowrap" style="justify-content:space-between;margin-top:.5rem">
                    <span class="ep-small">Compte</span>
                    <x-ep.badge :tone="$user->is_active ? 'green' : 'neutral'"
                                :label="$user->is_active ? 'Actif' : 'Désactivé'" />
                </div>
            </x-ep.card>
        </div>
    </div>
@endsection
