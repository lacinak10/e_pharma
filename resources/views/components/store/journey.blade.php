@props(['variant' => 'cards'])

@php
    /*
     * Le parcours client, déclaré une seule fois.
     *
     * L'accueil et « Comment ça marche » portaient chacun leur propre copie du
     * tableau : les deux pouvaient annoncer des parcours différents au même
     * client. Les deux gabarits diffèrent (cartes détachées ici, bande d'un
     * seul tenant là), pas le contenu.
     */
    $steps = [
        ['1', '#0E5C43', "J'ajoute mes médicaments au panier",
                         "Ou je téléverse simplement mon ordonnance."],
        ['2', '#0E5C43', "J'effectue mon paiement en ligne",
                         'Mobile money — Wave, Orange, MTN, Moov — ou carte bancaire.'],
        ['3', '#B87514', 'Validation de la commande',
                         'Nous appelons nos pharmacies partenaires et confirmons la disponibilité en moins de 5 minutes.'],
        ['4', '#33557F', 'Livraison',
                         "Un livreur récupère vos médicaments et vous les apporte, suivi en direct."],
    ];
@endphp

@if($variant === 'strip')
    <div class="ep-card ep-grid" style="grid-template-columns:repeat(auto-fit,minmax(min(100%,200px),1fr));gap:0">
        @foreach($steps as [$n, $color, $title, $text])
            <div style="padding:1.75rem 1.5rem 1.875rem;border-right:1px solid var(--ep-rule-soft);min-width:0">
                <p class="ep-display" style="font-size:2.125rem;color:{{ $color }};margin:0">{{ $n }}</p>
                <p style="font-size:.96875rem;font-weight:650;line-height:1.3;margin:1rem 0 .5rem">{{ $title }}</p>
                <p class="ep-small">{{ $text }}</p>
            </div>
        @endforeach
    </div>
@else
    <div class="ep-grid ep-grid--cards">
        @foreach($steps as [$n, $color, $title, $text])
            <article class="ep-card" style="padding:1.5rem">
                <p class="ep-display" style="font-size:2.125rem;color:{{ $color }};margin:0">{{ $n }}</p>
                <h2 style="font-size:.96875rem;font-weight:650;line-height:1.3;margin:1rem 0 .5rem">{{ $title }}</h2>
                <p class="ep-small" style="margin:0">{{ $text }}</p>
            </article>
        @endforeach
    </div>
@endif
