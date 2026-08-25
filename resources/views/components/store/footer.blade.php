@php $rating = $storeRating ?? ['average' => 0, 'count' => 0]; @endphp

<footer class="ep-footer">
    <div class="ep-shell">
        <div class="ep-footer__grid">
            <div>
                <div class="ep-row ep-row--nowrap" style="color:#fff;margin-bottom:1rem">
                    <span class="ep-sidebar__mark" aria-hidden="true">e</span>
                    <span class="ep-logo__word">ePharma</span>
                </div>
                <p style="font-size:.84375rem;line-height:1.6;margin:0 0 1.25rem;max-width:34ch">
                    La disponibilité vérifiée avant le déplacement.
                    {{ $partnerCount ?? 0 }} pharmacies partenaires à Abidjan.
                </p>
                @if($rating['count'] > 0)
                    <div class="ep-row ep-row--nowrap" style="gap:.625rem">
                        <span style="color:#F0B44A;letter-spacing:1px;font-size:.875rem">★★★★★</span>
                        <span class="ep-mono" style="font-size:.78125rem;color:#fff">{{ number_format($rating['average'], 1, ',', ' ') }}</span>
                        <a href="{{ route('store.reviews') }}">Avis clients</a>
                    </div>
                @endif
            </div>

            <div>
                <p class="ep-footer__col-title">La plateforme</p>
                <div class="ep-footer__links">
                    <a href="{{ route('store.how') }}">Comment ça marche</a>
                    <a href="{{ route('store.partners') }}">Nos partenaires</a>
                    <a href="{{ route('store.reviews') }}">Avis clients</a>
                </div>
            </div>

            <div>
                <p class="ep-footer__col-title">Commander</p>
                <div class="ep-footer__links">
                    <a href="{{ route('store.medicines.index') }}">Médicaments</a>
                    <a href="{{ route('store.prescriptions.create') }}">Téléverser une ordonnance</a>
                    <a href="{{ route('store.orders.index') }}">Mes commandes</a>
                    <a href="{{ route('store.cart.index') }}">Mon panier</a>
                </div>
            </div>

            <div>
                <p class="ep-footer__col-title">Aide</p>
                <div class="ep-footer__links">
                    <a href="{{ route('store.how') }}">FAQ</a>
                    <a href="{{ route('store.partners') }}">Zones et délais de livraison</a>
                    <a href="tel:+2252722000000">Nous contacter</a>
                </div>
            </div>

            <div>
                <p class="ep-footer__col-title">Légal</p>
                <div class="ep-footer__links">
                    <a href="{{ route('store.how') }}">Mentions légales</a>
                    <a href="{{ route('store.how') }}">CGV</a>
                    <a href="{{ route('store.how') }}">Protection des données</a>
                </div>
            </div>
        </div>

        <div class="ep-footer__bottom">
            <span>© {{ date('Y') }} ePharma. Les médicaments ne sont pas des produits ordinaires — demandez conseil à un pharmacien.</span>
            <span class="ep-mono">Abidjan · Côte d'Ivoire</span>
        </div>
    </div>
</footer>
