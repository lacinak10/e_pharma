@props(['order'])

@php
    /** @var \App\Models\Order $order */
    $courier = $order->assignment?->courier;
    $review  = $order->review;
    $current = $review?->rating ?? 0;
    $tags    = $review?->tags ?? [];
@endphp

<form method="POST" action="{{ route('store.orders.review', $order) }}" {{ $attributes }}>
    @csrf

    @if($courier)
        <p class="ep-small" style="margin:0 0 1rem">
            Comment s'est passée votre livraison avec <strong>{{ $courier->short_name }}</strong> ?
        </p>
    @endif

    <fieldset style="border:0;padding:0;margin:0 0 1rem">
        <legend class="ep-sr-only">Note sur 5</legend>
        {{-- L'ordre est inversé (direction rtl) pour que le survol allume les étoiles précédentes. --}}
        <div class="ep-rating">
            @foreach([5, 4, 3, 2, 1] as $value)
                <input type="radio" name="rating" id="rating-{{ $order->id }}-{{ $value }}"
                       value="{{ $value }}" @checked($current === $value) required>
                <label for="rating-{{ $order->id }}-{{ $value }}"
                       title="{{ $value }} étoile{{ $value > 1 ? 's' : '' }} sur 5">
                    ★<span class="ep-sr-only">{{ $value }} sur 5</span>
                </label>
            @endforeach
        </div>
        <x-input-error :messages="$errors->get('rating')" class="ep-error" />
    </fieldset>

    <div class="ep-row" style="margin-bottom:1rem">
        @foreach(\App\Models\CourierReview::TAGS as $tag)
            <label class="ep-chip">
                <input type="checkbox" name="tags[]" value="{{ $tag }}" @checked(in_array($tag, $tags, true))>
                {{ $tag }}
            </label>
        @endforeach
    </div>

    <div class="ep-field" style="margin-bottom:1rem">
        <label class="ep-label" for="comment-{{ $order->id }}">Votre commentaire</label>
        <textarea class="ep-textarea" id="comment-{{ $order->id }}" name="comment" maxlength="1000"
                  placeholder="Ponctualité, emballage, contact…">{{ old('comment', $review?->comment) }}</textarea>
        <x-input-error :messages="$errors->get('comment')" class="ep-error" />
    </div>

    <button type="submit" class="ep-btn ep-btn--primary ep-btn--block">
        {{ $review ? 'Modifier ma note' : 'Envoyer ma note' }}
    </button>
</form>
