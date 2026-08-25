@extends('layouts.store')

@section('title', 'Avis clients — ePharma')

@section('content')
    <section class="ep-shell ep-section">
        <div class="ep-split">
            <div>
                <p class="ep-eyebrow ep-eyebrow--green">Avis de nos clients</p>
                <div class="ep-row" style="align-items:flex-end;gap:.75rem;margin-top:1rem">
                    <span class="ep-display" style="font-size:clamp(3rem,2rem + 4vw,4.125rem);line-height:.85">
                        {{ $count > 0 ? number_format($average, 1, ',', ' ') : '—' }}
                    </span>
                    <span class="ep-small" style="padding-bottom:.5rem">sur 5</span>
                </div>
                <p class="ep-stars" style="font-size:1.0625rem;letter-spacing:2px;margin-top:.625rem">★★★★★</p>
                <p class="ep-small" style="margin-top:.375rem">{{ $count }} avis laissés après livraison</p>

                <div class="ep-stack" style="gap:.4375rem;margin-top:1.375rem;max-width:320px">
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
            </div>

            <div class="ep-grid ep-grid--wide">
                @forelse($reviews as $review)
                    <article class="ep-card" style="background:var(--ep-bg);padding:1.375rem">
                        <div class="ep-row ep-row--nowrap" style="justify-content:space-between">
                            <span class="ep-stars" style="font-size:.875rem;letter-spacing:1.5px">{{ $review->stars }}</span>
                            <time class="ep-mono ep-small" datetime="{{ $review->created_at->toDateString() }}">
                                {{ $review->created_at->locale('fr')->isoFormat('D MMM YYYY') }}
                            </time>
                        </div>
                        <p class="ep-body" style="margin:.875rem 0 1rem">« {{ $review->comment }} »</p>
                        <p class="ep-small" style="margin:0">
                            {{ $review->client?->short_name }}
                            @if($review->order?->delivery_address) · {{ \Illuminate\Support\Str::before($review->order->delivery_address, ',') }} @endif
                            · livreur {{ $review->courier?->short_name }}
                        </p>
                    </article>
                @empty
                    <x-ep.empty title="Aucun avis pour le moment."
                                text="Les avis apparaissent ici dès que les clients notent leur livreur." />
                @endforelse
            </div>
        </div>

        @if($reviews->hasPages())
            <div style="margin-top:2rem">{{ $reviews->links() }}</div>
        @endif
    </section>
@endsection
