@props([
    'order',
    'downloadUrl' => null,
])

@php
    /** @var \App\Models\Order $order */
    $reference = 'RX-' . str_pad((string) $order->id, 4, '0', STR_PAD_LEFT);
    $scope     = $order->prescription_scope === 'partial'
        ? "Seulement une partie des médicaments"
        : "Tous les médicaments de l'ordonnance";
@endphp

<div {{ $attributes->merge(['class' => 'ep-card']) }} data-ep-viewer>
    <div class="ep-viewer__stage">
        @if($downloadUrl)
            <img src="{{ $downloadUrl }}" alt="Ordonnance téléversée par le client" data-ep-viewer-image loading="lazy">
        @else
            <img src="{{ asset('assets/images/photos/ordonnance.jpg') }}" alt="Aperçu d'ordonnance" data-ep-viewer-image loading="lazy">
        @endif
    </div>

    <div class="ep-viewer__bar">
        <button type="button" class="ep-viewer__btn" data-ep-zoom="out" aria-label="Réduire">−</button>
        <button type="button" class="ep-viewer__btn" data-ep-zoom="in" aria-label="Agrandir">+</button>
        <button type="button" class="ep-viewer__btn" data-ep-zoom="rotate" aria-label="Pivoter">↻</button>
        @if($downloadUrl)
            <a href="{{ $downloadUrl }}" class="ep-viewer__btn" download>Télécharger</a>
        @endif
        <span class="ep-spacer ep-mono ep-small">{{ $reference }}</span>
    </div>

    <div class="ep-card__body" style="border-top:1px solid var(--ep-rule-soft)">
        <p class="ep-eyebrow">Périmètre demandé par le client</p>
        <p style="font-size:.875rem;font-weight:650;margin:.5rem 0 0">{{ $scope }}</p>

        @if($order->prescription_comment)
            <p class="ep-quote" style="margin-top:.75rem">« {{ $order->prescription_comment }} »</p>
        @endif
    </div>
</div>
