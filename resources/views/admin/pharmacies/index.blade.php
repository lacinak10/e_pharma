@extends('layouts.admin')

@section('title', 'Pharmacies partenaires — ePharma')
@section('page_title', 'Pharmacies partenaires')
@section('page_subtitle', 'Le réseau que vous appelez pour confirmer la disponibilité')

@section('content')
    <x-ep.card flush>
        <header class="ep-card__head">
            <h2 class="ep-card__title">Réseau partenaire</h2>
            <form method="GET" class="ep-row" style="gap:.375rem">
                <label class="ep-sr-only" for="q">Rechercher</label>
                <input class="ep-input" id="q" name="q" value="{{ request('q') }}"
                       placeholder="Nom ou quartier…" style="width:200px;padding:.5rem .75rem;font-size:.8125rem">
                <button type="submit" class="ep-btn ep-btn--ghost ep-btn--sm">Filtrer</button>
            </form>
            <a href="{{ route('manager.pharmacies.create') }}" class="ep-spacer ep-btn ep-btn--primary ep-btn--md">Ajouter une pharmacie</a>
        </header>

        <div class="ep-table-wrap">
            <table class="ep-table ep-table--cards">
                <thead>
                    <tr>
                        <th scope="col">Pharmacie</th>
                        <th scope="col">Quartier</th>
                        <th scope="col">Téléphone</th>
                        <th scope="col">Fiabilité</th>
                        <th scope="col">Réponse</th>
                        <th scope="col">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pharmacies as $pharmacy)
                        <tr>
                            <td data-label="Pharmacie">
                                <p style="font-size:.84375rem;font-weight:650;margin:0">{{ $pharmacy->name }}</p>
                                @unless($pharmacy->is_active)
                                    <span class="ep-badge ep-badge--neutral">Désactivée</span>
                                @endunless
                            </td>
                            <td data-label="Quartier"><span class="ep-small">{{ $pharmacy->area_label }}</span></td>
                            <td data-label="Téléphone">
                                <a href="tel:{{ preg_replace('/\s+/', '', $pharmacy->phone) }}" class="ep-mono ep-small">{{ $pharmacy->phone }}</a>
                            </td>
                            <td data-label="Fiabilité">
                                <span class="ep-row ep-row--nowrap" style="gap:.5rem">
                                    <span class="ep-meter"><span style="width:{{ $pharmacy->reliability }}%;background:{{ $pharmacy->reliability_color }}"></span></span>
                                    <span class="ep-mono ep-small">{{ $pharmacy->reliability }} %</span>
                                </span>
                            </td>
                            <td data-label="Réponse"><span class="ep-cell-num">{{ $pharmacy->avg_response_minutes }} min</span></td>
                            <td data-label="Action">
                                <div class="ep-cell-actions">
                                    <a href="{{ route('manager.pharmacies.edit', $pharmacy) }}" class="ep-btn ep-btn--ghost ep-btn--sm">Modifier</a>
                                    @if($pharmacy->is_active)
                                        <form method="POST" action="{{ route('manager.pharmacies.destroy', $pharmacy) }}">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="ep-btn ep-btn--danger-soft ep-btn--sm">Désactiver</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" data-label=""><span class="ep-small">Aucune pharmacie partenaire.</span></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-ep.card>

    @if($pharmacies->hasPages())
        {{ $pharmacies->links() }}
    @endif
@endsection
