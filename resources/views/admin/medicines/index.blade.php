@extends('layouts.admin')

@section('title', 'Médicaments — ePharma')
@section('page_title', 'Référentiel des médicaments')
@section('page_subtitle', 'Ce que l’on sait commander · la disponibilité est vérifiée commande par commande')

@section('content')
    <x-ep.card flush>
        <header class="ep-card__head">
            <h2 class="ep-card__title">{{ $medicines->total() }} médicament(s)</h2>

            <form method="GET" class="ep-row ep-spacer" style="gap:.375rem">
                <label class="ep-sr-only" for="q">Nom</label>
                <input class="ep-input" id="q" name="q" value="{{ $q }}" placeholder="Nom du médicament…"
                       style="width:180px;padding:.5rem .75rem;font-size:.8125rem">

                <label class="ep-sr-only" for="category">Catégorie</label>
                <select class="ep-select" id="category" name="category" style="width:auto;padding:.5rem .75rem;font-size:.8125rem">
                    <option value="">Toutes les catégories</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->slug }}" @selected($category === $cat->slug)>{{ $cat->name }}</option>
                    @endforeach
                </select>

                <label class="ep-chip">
                    <input type="checkbox" name="rx" value="1" @checked(request()->boolean('rx'))>
                    Sur ordonnance
                </label>

                <button type="submit" class="ep-btn ep-btn--ghost ep-btn--sm">Filtrer</button>
            </form>

            <a href="{{ route('manager.medicines.create') }}" class="ep-btn ep-btn--primary ep-btn--md">Ajouter</a>
        </header>

        @if($medicines->isEmpty())
            <x-ep.empty title="Aucun médicament au référentiel."
                        text="Ajoutez les médicaments que vos pharmacies partenaires savent délivrer." />
        @else
            <div class="ep-table-wrap">
                <table class="ep-table ep-table--cards">
                    <thead>
                        <tr>
                            <th scope="col" colspan="2">Médicament</th>
                            <th scope="col">Catégorie</th>
                            <th scope="col">Disponibilité constatée</th>
                            <th scope="col">Prix indicatif</th>
                            <th scope="col">Catalogue</th>
                            <th scope="col">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($medicines as $medicine)
                            @php $signal = $medicine->availabilitySignal(); @endphp
                            <tr>
                                <td data-label="" style="width:52px">
                                    <img src="{{ $medicine->image_src }}" alt=""
                                         style="width:44px;height:44px;object-fit:cover;border-radius:var(--ep-radius-sm)">
                                </td>
                                <td data-label="Médicament" class="ep-cell-name">
                                    <p style="font-size:.84375rem;font-weight:650;margin:0">
                                        {{ $medicine->name }}
                                        @if($medicine->requires_prescription)<span class="ep-badge ep-badge--rx">RX</span>@endif
                                    </p>
                                    <p class="ep-mono ep-small" style="margin:0">{{ $medicine->pack_label }}</p>
                                </td>
                                <td data-label="Catégorie"><span class="ep-small">{{ $medicine->category?->name ?? '—' }}</span></td>
                                <td data-label="Disponibilité">
                                    <x-ep.badge :tone="$signal['tone']" :label="$signal['label']" />
                                    <p class="ep-small" style="margin:.25rem 0 0">
                                        {{ $medicine->checks_count }} vérification(s) sur {{ \App\Models\Medicine::SIGNAL_WINDOW_DAYS }} j
                                    </p>
                                </td>
                                <td data-label="Prix"><span class="ep-cell-num">{{ number_format($medicine->price, 0, ',', ' ') }} F</span></td>
                                <td data-label="Catalogue">
                                    <x-ep.badge :tone="$medicine->is_active ? 'green' : 'neutral'"
                                                :label="$medicine->is_active ? 'Référencé' : 'Retiré'" />
                                </td>
                                <td data-label="Action">
                                    <div class="ep-cell-actions">
                                        <a href="{{ route('manager.medicines.edit', $medicine) }}" class="ep-btn ep-btn--ghost ep-btn--sm">Modifier</a>
                                        <form method="POST" action="{{ route('manager.medicines.toggle', $medicine) }}">
                                            @csrf @method('PATCH')
                                            <button type="submit" class="ep-btn ep-btn--ghost ep-btn--sm">
                                                {{ $medicine->is_active ? 'Retirer' : 'Réactiver' }}
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </x-ep.card>

    @if($medicines->hasPages()) {{ $medicines->links() }} @endif
@endsection
