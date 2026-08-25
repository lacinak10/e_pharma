@extends('layouts.admin')

@section('title', 'Avis et notes — ePharma')
@section('page_title', 'Avis et notes')
@section('page_subtitle', 'Ce que les clients disent de leurs livreurs')

@section('content')
    <div class="ep-split">
        <div class="ep-stack">
            <x-ep.card flush>
                <header class="ep-card__head">
                    <h2 class="ep-card__title">Derniers avis</h2>
                    <form method="GET" class="ep-row ep-spacer" style="gap:.375rem">
                        <label class="ep-sr-only" for="courier">Livreur</label>
                        <select class="ep-select" id="courier" name="courier" style="width:auto;padding:.5rem .75rem;font-size:.8125rem">
                            <option value="">Tous les livreurs</option>
                            @foreach($couriers as $courier)
                                <option value="{{ $courier->id }}" @selected(request('courier') == $courier->id)>{{ $courier->name }}</option>
                            @endforeach
                        </select>
                        <label class="ep-sr-only" for="rating">Note</label>
                        <select class="ep-select" id="rating" name="rating" style="width:auto;padding:.5rem .75rem;font-size:.8125rem">
                            <option value="">Toutes les notes</option>
                            @foreach(range(5, 1) as $star)
                                <option value="{{ $star }}" @selected(request('rating') == $star)>{{ $star }} étoile(s)</option>
                            @endforeach
                        </select>
                        <button type="submit" class="ep-btn ep-btn--ghost ep-btn--sm">Filtrer</button>
                    </form>
                </header>

                @if($reviews->isEmpty())
                    <x-ep.empty title="Aucun avis pour l'instant."
                                text="Les notes apparaissent ici dès qu'un client évalue son livreur après réception." />
                @else
                    <div style="padding:.5rem 0">
                        @foreach($reviews as $review)
                            <article style="padding:1rem 1.125rem;border-bottom:1px solid var(--ep-rule-soft)">
                                <div class="ep-row ep-row--nowrap" style="justify-content:space-between">
                                    <span class="ep-stars" style="font-size:.875rem;letter-spacing:1.5px">{{ $review->stars }}</span>
                                    <time class="ep-mono ep-small">{{ $review->created_at->locale('fr')->isoFormat('D MMM YYYY · HH:mm') }}</time>
                                </div>
                                @if($review->comment)
                                    <p class="ep-body" style="margin:.75rem 0 .625rem">« {{ $review->comment }} »</p>
                                @endif
                                <div class="ep-row" style="gap:.375rem">
                                    @foreach($review->tags ?? [] as $tag)
                                        <span class="ep-chip">{{ $tag }}</span>
                                    @endforeach
                                </div>
                                <p class="ep-small" style="margin:.625rem 0 0">
                                    {{ $review->client?->short_name }} · livreur <strong>{{ $review->courier?->short_name }}</strong>
                                </p>
                            </article>
                        @endforeach
                    </div>
                @endif
            </x-ep.card>

            @if($reviews->hasPages()) {{ $reviews->links() }} @endif
        </div>

        <x-ep.card title="Satisfaction globale">
            <div class="ep-row" style="align-items:flex-end;gap:.75rem">
                <span class="ep-display" style="font-size:3.25rem;line-height:.85">
                    {{ $count > 0 ? number_format($average, 1, ',', ' ') : '—' }}
                </span>
                <span class="ep-small" style="padding-bottom:.5rem">sur 5</span>
            </div>
            <p class="ep-small" style="margin-top:.5rem">{{ $count }} avis après livraison</p>

            <div class="ep-stack" style="gap:.4375rem;margin-top:1.25rem">
                @foreach($distribution as $star => $percent)
                    <div class="ep-row ep-row--nowrap" style="gap:.625rem">
                        <span class="ep-mono ep-small" style="width:14px">{{ $star }}</span>
                        <span style="flex:1;height:7px;border-radius:99px;background:var(--ep-rule-soft);overflow:hidden">
                            <span style="display:block;height:100%;width:{{ $percent }}%;background:var(--ep-amber)"></span>
                        </span>
                        <span class="ep-mono ep-small" style="width:42px;text-align:right">{{ $percent }} %</span>
                    </div>
                @endforeach
            </div>
        </x-ep.card>
    </div>
@endsection
