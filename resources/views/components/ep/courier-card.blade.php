@props([
    'courier',
    'eta'     => null,   // minutes
    'actions' => true,
])

@php
    /** @var \App\Models\User $courier */
    $rating = $courier->rating;
    $count  = $courier->reviews_count ?? $courier->reviews()->count();
@endphp

<div {{ $attributes->merge(['class' => 'ep-courier']) }}>
    <img src="{{ $courier->avatar_url }}" alt="Portrait de {{ $courier->short_name }}" class="ep-courier__avatar" loading="lazy">

    <div style="min-width:0;flex:1">
        <p class="ep-courier__name">{{ $courier->short_name }}</p>
        <div class="ep-courier__meta">
            <span class="ep-stars" aria-hidden="true">{{ $courier->stars }}</span>
            <span class="ep-mono ep-small">
                {{ $rating ? number_format($rating, 1, ',', ' ') : 'Nouveau' }}
                @if($count) · {{ $count }} course{{ $count > 1 ? 's' : '' }} @endif
            </span>
        </div>
        @if($eta)
            <p class="ep-courier__eta">Arrivée estimée · {{ $eta }} min</p>
        @endif
    </div>

    @if($actions && $courier->phone)
        <div class="ep-courier__actions">
            <a href="tel:{{ preg_replace('/\s+/', '', $courier->phone) }}" class="ep-btn ep-btn--primary ep-btn--md">Appeler</a>
            <a href="sms:{{ preg_replace('/\s+/', '', $courier->phone) }}" class="ep-btn ep-btn--ghost ep-btn--md">Message</a>
        </div>
    @endif
</div>
