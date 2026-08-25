@extends('layouts.admin')

@section('title', 'Livreurs — ePharma')
@section('page_title', 'Livreurs')
@section('page_subtitle', $couriers->total() . ' livreur(s) · note moyenne et charge en cours')

@section('content')
    <x-ep.card flush>
        <header class="ep-card__head">
            <h2 class="ep-card__title">Équipe de livraison</h2>

            <form method="GET" class="ep-row ep-spacer" style="gap:.375rem">
                <label class="ep-sr-only" for="q">Recherche</label>
                <input class="ep-input" id="q" name="q" value="{{ $filters['q'] }}" placeholder="Nom, e-mail, téléphone…"
                       style="width:200px;padding:.5rem .75rem;font-size:.8125rem">
                <label class="ep-sr-only" for="status">État</label>
                <select class="ep-select" id="status" name="status" style="width:auto;padding:.5rem .75rem;font-size:.8125rem">
                    <option value="">Tous</option>
                    <option value="active" @selected($filters['status'] === 'active')>Actifs</option>
                    <option value="inactive" @selected($filters['status'] === 'inactive')>Désactivés</option>
                </select>
                <button type="submit" class="ep-btn ep-btn--ghost ep-btn--sm">Filtrer</button>
            </form>

            <a href="{{ route('manager.couriers.create') }}" class="ep-btn ep-btn--primary ep-btn--md">Ajouter</a>
        </header>

        @if($couriers->isEmpty())
            <x-ep.empty title="Aucun livreur." text="Ajoutez un livreur pour pouvoir attribuer les courses." />
        @else
            <div class="ep-table-wrap">
                <table class="ep-table ep-table--cards">
                    <thead>
                        <tr>
                            <th scope="col" colspan="2">Livreur</th>
                            <th scope="col">Contact</th>
                            <th scope="col">Zone</th>
                            <th scope="col">Note</th>
                            <th scope="col">Courses</th>
                            <th scope="col">État</th>
                            <th scope="col">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($couriers as $courier)
                            @php $stats = $statsByCourier[$courier->id] ?? null; @endphp
                            <tr>
                                <td data-label="" style="width:52px">
                                    <img src="{{ $courier->avatar_url }}" alt=""
                                         style="width:36px;height:36px;border-radius:50%;object-fit:cover">
                                </td>
                                <td data-label="Livreur" class="ep-cell-name">
                                    <p style="font-size:.84375rem;font-weight:650;margin:0">{{ $courier->name }}</p>
                                    <p class="ep-small" style="margin:0">{{ $courier->courierState() }}</p>
                                </td>
                                <td data-label="Contact">
                                    <p class="ep-small" style="margin:0">{{ $courier->email }}</p>
                                    @if($courier->phone)
                                        <a href="tel:{{ preg_replace('/\s+/', '', $courier->phone) }}" class="ep-mono ep-small">{{ $courier->phone }}</a>
                                    @endif
                                </td>
                                <td data-label="Zone"><span class="ep-small">{{ $courier->zone ?? '—' }}</span></td>
                                <td data-label="Note">
                                    <span class="ep-row ep-row--nowrap" style="gap:.375rem">
                                        <span class="ep-stars" aria-hidden="true">{{ $courier->stars }}</span>
                                        <span class="ep-mono ep-small">
                                            {{ $courier->rating ? number_format($courier->rating, 1, ',', ' ') : '—' }}
                                            ({{ $courier->reviews_count }})
                                        </span>
                                    </span>
                                </td>
                                <td data-label="Courses">
                                    <span class="ep-cell-num">
                                        {{ $stats->delivered ?? 0 }} livrée(s)
                                        @if(($stats->in_progress ?? 0) > 0) · {{ $stats->in_progress }} en cours @endif
                                    </span>
                                </td>
                                <td data-label="État">
                                    <x-ep.badge :tone="$courier->is_active ? 'green' : 'neutral'"
                                                :label="$courier->is_active ? 'Actif' : 'Désactivé'" />
                                </td>
                                <td data-label="Action">
                                    <div class="ep-cell-actions">
                                        <a href="{{ route('manager.couriers.show', $courier) }}" class="ep-btn ep-btn--ghost ep-btn--sm">Fiche</a>
                                        <a href="{{ route('manager.couriers.edit', $courier) }}" class="ep-btn ep-btn--ghost ep-btn--sm">Modifier</a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </x-ep.card>

    @if($couriers->hasPages()) {{ $couriers->links() }} @endif
@endsection
