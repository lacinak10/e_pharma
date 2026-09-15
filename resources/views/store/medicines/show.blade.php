@extends('layouts.store')

@section('title', $medicine->name . ' — ePharma')
@section('description', $medicine->indication ?: $medicine->description)

@section('content')
<div class="ep-shell ep-section--tight">

    <nav class="ep-small" style="margin-bottom:1.25rem">
        <a href="{{ route('store.medicines.index') }}">Médicaments</a>
        @if($medicine->category)
            <span aria-hidden="true"> · </span>
            <a href="{{ route('store.medicines.index', ['category' => $medicine->category->slug]) }}">{{ $medicine->category->name }}</a>
        @endif
    </nav>

    <div class="ep-split">
        <div class="ep-stack">
            <div class="ep-grid ep-grid--2" style="gap:2rem;align-items:start">
                <div class="ep-card" style="position:relative;padding:1.5rem">
                    <img src="{{ $medicine->image_src }}" alt="{{ $medicine->name }}"
                         style="width:100%;aspect-ratio:1/1;object-fit:cover;border-radius:var(--ep-radius-sm)">
                    @if($medicine->requires_prescription)
                        <span class="ep-badge ep-badge--rx" style="position:absolute;top:1rem;left:1rem">Sur ordonnance</span>
                    @endif
                </div>

                <div>
                    <h1 class="ep-h3">{{ $medicine->name }}</h1>

                    @if($medicine->indication)
                        <p class="ep-body" style="margin-top:.625rem">{{ $medicine->indication }}</p>
                    @endif

                    <p class="ep-mono ep-small" style="margin-top:.75rem">{{ $medicine->pack_label }}</p>

                    @php $signal = $medicine->availabilitySignal(); @endphp
                    <p style="display:flex;align-items:center;gap:.5rem;margin-top:1rem;font-size:.875rem;font-weight:600;color:{{ $signal['color'] }}">
                        <span style="width:8px;height:8px;border-radius:50%;background:currentColor"></span>
                        {{ $signal['label'] }}
                    </p>
                    <p class="ep-hint" style="margin:.375rem 0 0;max-width:44ch">
                        Constat des {{ \App\Models\Medicine::SIGNAL_WINDOW_DAYS }} derniers jours. La disponibilité
                        réelle est confirmée par téléphone après votre commande.
                    </p>

                    <p style="margin:1.25rem 0 1.5rem">
                        <span class="ep-hint" style="display:block">Prix indicatif</span>
                        <span class="ep-mono" style="font-size:1.75rem;font-weight:600">
                            {{ number_format($medicine->price, 0, ',', ' ') }} F
                        </span>
                    </p>

                    <form method="POST" action="{{ route('store.cart.add', $medicine) }}" class="ep-row" style="gap:.5rem">
                        @csrf
                        <label class="ep-sr-only" for="qty">Quantité</label>
                        <input class="ep-input ep-mono" id="qty" type="number" name="qty" value="1"
                               min="1" max="99" style="width:88px">
                        <button type="submit" class="ep-btn ep-btn--primary" style="flex:1;min-width:150px">
                            Ajouter au panier
                        </button>
                    </form>

                    @if($medicine->requires_prescription)
                        <p class="ep-flash ep-flash--error" style="margin-top:1.25rem">
                            Ce médicament ne peut être délivré que sur présentation d'une ordonnance.
                            <a href="{{ route('store.prescriptions.create') }}">Téléverser mon ordonnance</a>
                        </p>
                    @endif
                </div>
            </div>

            @if($medicine->description)
                <x-ep.card title="Description">
                    <p class="ep-body" style="margin:0">{{ $medicine->description }}</p>
                </x-ep.card>
            @endif

            @if($related->isNotEmpty())
                <section>
                    <h2 class="ep-h4" style="margin-bottom:1rem">Dans la même catégorie</h2>
                    <div class="ep-grid ep-grid--cards">
                        @foreach($related as $item)
                            <x-ep.product-card :medicine="$item" />
                        @endforeach
                    </div>
                </section>
            @endif
        </div>

        <div class="ep-stack">
            <x-ep.card title="La disponibilité, avant le déplacement">
                <p class="ep-small" style="margin:0 0 1rem">
                    ePharma ne détient aucun stock : nous ne pouvons donc pas vous promettre
                    qu'un médicament est en rayon. Après votre commande, un manager appelle nos
                    pharmacies partenaires une par une et vous donne le résultat
                    <strong>en moins de 5 minutes</strong> — avant tout déplacement.
                </p>
                <a href="{{ route('store.how') }}" class="ep-btn ep-btn--ghost ep-btn--block">Comment ça marche</a>
            </x-ep.card>

            <x-ep.card title="Conseil du pharmacien">
                <p class="ep-small" style="margin:0">
                    Ne jamais associer deux médicaments contenant la même molécule sans avis.
                    En cas de doute sur un dosage, appelez-nous au
                    <a href="tel:{{ $company['phone_href'] }}" class="ep-mono">{{ $company['phone'] }}</a>.
                </p>
            </x-ep.card>
        </div>
    </div>
</div>
@endsection
