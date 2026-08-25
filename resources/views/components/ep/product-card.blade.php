@props(['medicine'])

@php
    /** @var \App\Models\Medicine $medicine */
    $url    = route('store.medicines.show', $medicine);
    $signal = $medicine->availabilitySignal();
@endphp

<article {{ $attributes->merge(['class' => 'ep-product']) }}>
    <div class="ep-product__media">
        <a href="{{ $url }}" tabindex="-1" aria-hidden="true">
            <img src="{{ $medicine->image_src }}" alt="" loading="lazy">
        </a>
        @if($medicine->requires_prescription)
            <span class="ep-badge ep-badge--rx ep-product__rx">Sur ordonnance</span>
        @endif
    </div>

    <div class="ep-product__body">
        <h3 style="margin:0">
            <a href="{{ $url }}" class="ep-product__name">{{ $medicine->name }}</a>
        </h3>

        @if($medicine->indication)
            <p class="ep-product__ind">{{ $medicine->indication }}</p>
        @endif

        <p class="ep-product__pack">{{ $medicine->pack_label }}</p>

        {{-- Disponibilité constatée lors des vérifications récentes, jamais un stock. --}}
        <p class="ep-product__avail" style="color: {{ $signal['color'] }}">{{ $signal['label'] }}</p>

        <div class="ep-product__foot">
            <div>
                <span class="ep-hint" style="display:block">Prix indicatif</span>
                <span class="ep-product__price">{{ number_format($medicine->price, 0, ',', ' ') }} F</span>
            </div>

            <form method="POST" action="{{ route('store.cart.add', $medicine) }}">
                @csrf
                <button type="submit" class="ep-btn ep-btn--primary ep-btn--md">Ajouter</button>
            </form>
        </div>
    </div>
</article>
