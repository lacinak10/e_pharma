@extends('layouts.store')

@section('title', 'ePharma — la disponibilité vérifiée avant le déplacement')
@section('description', 'Commandez vos médicaments à Abidjan. Nous appelons nos pharmacies partenaires et vous savez en moins de 5 minutes si votre médicament est disponible.')

@section('content')

{{-- ── Hero ───────────────────────────────────────────────────────── --}}
<section style="background:var(--ep-surface);border-bottom:1px solid var(--ep-rule)">
    <div class="ep-shell ep-section">
        <div class="ep-grid ep-grid--2" style="align-items:center">
            <div>
                <p class="ep-badge ep-badge--green" style="margin-bottom:1.625rem">
                    {{ $partnerCount ?? 0 }} pharmacies partenaires · Abidjan
                </p>

                <h1 class="ep-h1" style="max-width:16ch">
                    Ne vous déplacez plus pour découvrir que votre médicament n'est pas disponible.
                </h1>

                <p class="ep-lead" style="max-width:52ch;margin:1.375rem 0 2rem">
                    Vous commandez, nous appelons nos pharmacies partenaires, et vous savez en moins de
                    5 minutes si votre médicament est là. Ensuite un livreur le récupère et vous l'apporte.
                </p>

                <form method="GET" action="{{ route('store.medicines.index') }}" style="max-width:560px">
                    <label for="hero-q" class="ep-sr-only">Nom du médicament, molécule ou indication</label>
                    <div class="ep-search" style="border-color:var(--ep-ink);border-radius:12px">
                        <input type="search" id="hero-q" name="q"
                               placeholder="Nom du médicament, molécule ou indication">
                        <button type="submit">Chercher</button>
                    </div>
                </form>

                <div class="ep-row" style="margin-top:1.25rem">
                    <a href="{{ route('store.prescriptions.create') }}" class="ep-btn ep-btn--ghost">
                        <span aria-hidden="true">↑</span> J'ai une ordonnance
                    </a>
                    <span class="ep-small" style="max-width:34ch">
                        Photo, galerie ou PDF — vous précisez ensuite ce que vous voulez.
                    </span>
                </div>
            </div>

            <div style="position:relative">
                <img src="{{ asset('assets/images/photos/pharmacie.jpg') }}"
                     alt="Comptoir d'une pharmacie partenaire à Abidjan"
                     style="width:100%;height:clamp(280px,20rem + 8vw,520px);object-fit:cover;border-radius:18px;display:block">

                <div class="ep-card ep-hero-clock" style="position:absolute;bottom:2.75rem;width:min(300px,86%);padding:1.125rem 1.25rem;box-shadow:var(--ep-shadow)">
                    <p class="ep-eyebrow ep-eyebrow--amber">Vérification en cours</p>
                    <div class="ep-row" style="align-items:flex-end;gap:.75rem;margin-top:.5rem">
                        <span class="ep-display" style="font-size:3.25rem;line-height:.85;color:var(--ep-amber-deep);font-variant-numeric:tabular-nums">4:12</span>
                        <span class="ep-small" style="padding-bottom:.375rem;line-height:1.3">avant<br>le résultat</span>
                    </div>
                    <div style="height:5px;border-radius:99px;background:#F0EADC;overflow:hidden;margin-top:.875rem">
                        <div style="height:100%;width:83%;background:var(--ep-amber)"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ── Promesses ──────────────────────────────────────────────────── --}}
<section style="background:var(--ep-green);color:#EAF3EF">
    <div class="ep-shell" style="padding-block:1.625rem">
        <div class="ep-grid" style="grid-template-columns:repeat(auto-fit,minmax(min(100%,200px),1fr));gap:2rem">
            @foreach([
                ['01', 'Disponibilité vérifiée', 'en moins de 5 minutes'],
                ['02', ($partnerCount ?? 0) . ' pharmacies partenaires', 'contactées une par une'],
                ['03', 'Livraison à domicile', 'suivi des 5 étapes en direct'],
                ['04', 'Livreurs notés par vous', ($storeRating['count'] ?? 0) > 0 ? number_format($storeRating['average'], 1, ',', ' ') . ' sur 5 en moyenne' : 'après chaque livraison'],
            ] as [$n, $title, $sub])
                <div class="ep-row ep-row--nowrap" style="gap:.75rem;align-items:flex-start">
                    <span class="ep-mono" style="font-size:.75rem;color:#8FC6AE;padding-top:2px">{{ $n }}</span>
                    <div>
                        <p style="font-size:.90625rem;font-weight:650;color:#fff;margin:0">{{ $title }}</p>
                        <p style="font-size:.8125rem;color:#A9CFBE;margin:2px 0 0">{{ $sub }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ── Comment ça marche ──────────────────────────────────────────── --}}
<section class="ep-shell ep-section">
    <div class="ep-row" style="align-items:flex-end;justify-content:space-between;margin-bottom:2.5rem">
        <div>
            <p class="ep-eyebrow ep-eyebrow--green">Comment ça marche</p>
            <h2 class="ep-h2" style="max-width:24ch;margin-top:.75rem">
                Quatre étapes, et vous n'avez pas bougé de chez vous.
            </h2>
        </div>
        <a href="{{ route('store.how') }}" style="font-size:.90625rem;font-weight:650">Page complète →</a>
    </div>

    <x-store.journey variant="strip" />
</section>

{{-- ── Médicaments ────────────────────────────────────────────────── --}}
<section class="ep-shell" style="padding-bottom:clamp(2.5rem,1.5rem + 4vw,5rem)">
    <div class="ep-row" style="align-items:flex-end;justify-content:space-between;margin-bottom:1.75rem">
        <h2 class="ep-h3">Médicaments les plus commandés</h2>
        <span class="ep-mono ep-small">Disponibilité constatée sur les 24 dernières heures</span>
    </div>

    <div class="ep-grid ep-grid--cards">
        @forelse($featured as $medicine)
            <x-ep.product-card :medicine="$medicine" />
        @empty
            <x-ep.empty title="Le catalogue est vide."
                        text="Les médicaments apparaîtront ici dès qu'ils seront enregistrés." />
        @endforelse
    </div>

    <div style="display:flex;justify-content:center;margin-top:2rem">
        <a href="{{ route('store.medicines.index') }}" class="ep-btn ep-btn--ghost">Voir tous les médicaments</a>
    </div>
</section>

{{-- ── Ordonnance ─────────────────────────────────────────────────── --}}
<section style="background:var(--ep-surface);border-block:1px solid var(--ep-rule)">
    <div class="ep-shell ep-section">
        <div class="ep-grid ep-grid--2" style="align-items:center">
            <div>
                <p class="ep-eyebrow ep-eyebrow--green">Ordonnance</p>
                <h2 class="ep-h2" style="margin:1rem 0 1.125rem">Vous avez une ordonnance ?</h2>
                <p class="ep-lead" style="max-width:46ch;margin-bottom:1.5rem">
                    Envoyez-la en une photo. Vous précisez ensuite si vous voulez
                    <strong style="color:var(--ep-ink)">tous les médicaments</strong> ou
                    <strong style="color:var(--ep-ink)">seulement une partie</strong>, en indiquant lesquels.
                    Le manager reçoit l'ordonnance et votre commentaire.
                </p>
                <div class="ep-row">
                    <a href="{{ route('store.prescriptions.create') }}" class="ep-btn ep-btn--primary">Téléverser une ordonnance</a>
                    <a href="{{ route('store.orders.index') }}" class="ep-btn ep-btn--ghost">Mes commandes</a>
                </div>
            </div>

            <div class="ep-card" style="background:var(--ep-bg);padding:1.375rem">
                <div class="ep-row ep-row--nowrap" style="gap:1.125rem;align-items:flex-start">
                    <img src="{{ asset('assets/images/photos/ordonnance.jpg') }}" alt="Ordonnance photographiée"
                         style="width:132px;height:172px;object-fit:cover;border-radius:8px;border:1px solid var(--ep-rule);flex:none">
                    <div style="min-width:0;flex:1">
                        <p class="ep-eyebrow">Votre commentaire au manager</p>
                        <div class="ep-stack" style="gap:.5rem;margin-top:.875rem">
                            <span class="ep-choice">Tous les médicaments de l'ordonnance</span>
                            <span class="ep-choice" style="background:var(--ep-green-8);border:1.5px solid var(--ep-green);color:var(--ep-green-dark);font-weight:650">
                                Seulement une partie
                            </span>
                        </div>
                        <p class="ep-quote" style="margin-top:.75rem">
                            « Uniquement l'Amoxicilline et le sirop, j'ai déjà le Paracétamol. »
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ── Suivi en direct ────────────────────────────────────────────── --}}
<section class="ep-shell ep-section">
    <div class="ep-grid ep-grid--2" style="align-items:center">
        <div>
            <p class="ep-eyebrow ep-eyebrow--green">Suivi en direct</p>
            <h2 class="ep-h2" style="margin:1rem 0 1.125rem;max-width:20ch">
                Vous savez où en est votre livreur, étape par étape.
            </h2>
            <p class="ep-lead" style="max-width:44ch;margin-bottom:1.5rem">
                Dès qu'un livreur est assigné, vous recevez son nom, son numéro et le délai estimé.
                Puis les cinq étapes s'allument une à une.
            </p>
            <a href="{{ route('store.orders.index') }}" style="font-size:.9375rem;font-weight:650">Suivre ma commande →</a>
        </div>

        <div class="ep-card" style="padding:1.75rem 2rem">
            <ol class="ep-timeline" style="list-style:none;margin:0;padding:0">
                @foreach([
                    ['done', 'En route vers la pharmacie', 'Pharmacie du Plateau', '20:41'],
                    ['done', 'Arrivé à la pharmacie', 'Vérification du panier', '20:48'],
                    ['done', 'Médicament récupéré', '2 articles sur 2', '20:52'],
                    ['current', 'En route vers vous', 'Arrivée estimée 21:14', 'en cours'],
                    ['todo', 'Commande livrée', 'Code de confirmation à donner', '—'],
                ] as [$state, $label, $sub, $time])
                    <li class="ep-timeline__step ep-timeline__step--{{ $state }}">
                        <span class="ep-timeline__rail" aria-hidden="true">
                            <span class="ep-timeline__dot"></span>
                            <span class="ep-timeline__line"></span>
                        </span>
                        <div class="ep-timeline__content">
                            <p class="ep-timeline__label">{{ $label }}</p>
                            <p class="ep-timeline__sub">{{ $sub }}</p>
                        </div>
                        <span class="ep-timeline__time">{{ $time }}</span>
                    </li>
                @endforeach
            </ol>
        </div>
    </div>
</section>

{{-- ── Avis ───────────────────────────────────────────────────────── --}}
@if($testimonies->isNotEmpty())
<section style="background:var(--ep-surface);border-top:1px solid var(--ep-rule)">
    <div class="ep-shell ep-section">
        <div class="ep-row" style="align-items:flex-end;justify-content:space-between;margin-bottom:1.75rem">
            <div>
                <p class="ep-eyebrow ep-eyebrow--green">Avis de nos clients</p>
                <h2 class="ep-h3" style="margin-top:.75rem">Ce qu'ils disent après livraison</h2>
            </div>
            <a href="{{ route('store.reviews') }}" style="font-size:.90625rem;font-weight:650">Tous les avis →</a>
        </div>

        <div class="ep-grid ep-grid--wide">
            @foreach($testimonies as $review)
                <article class="ep-card" style="background:var(--ep-bg);padding:1.375rem">
                    <div class="ep-row ep-row--nowrap" style="justify-content:space-between">
                        <span class="ep-stars" style="font-size:.875rem;letter-spacing:1.5px">{{ $review->stars }}</span>
                        <time class="ep-mono ep-small">{{ $review->created_at->locale('fr')->isoFormat('D MMM YYYY') }}</time>
                    </div>
                    <p class="ep-body" style="margin:.875rem 0 1rem">« {{ $review->comment }} »</p>
                    <p class="ep-small" style="margin:0">
                        {{ $review->client?->short_name }} · livreur {{ $review->courier?->short_name }}
                    </p>
                </article>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- ── Partenaires ────────────────────────────────────────────────── --}}
<section class="ep-shell ep-section">
    <div class="ep-row" style="align-items:flex-end;justify-content:space-between;margin-bottom:1.75rem">
        <div>
            <p class="ep-eyebrow ep-eyebrow--green">Nos pharmacies partenaires</p>
            <h2 class="ep-h3" style="margin-top:.75rem">Celles que nous appelons pour vous</h2>
        </div>
        <a href="{{ route('store.partners') }}" style="font-size:.90625rem;font-weight:650">Voir le réseau complet →</a>
    </div>

    <div class="ep-grid ep-grid--wide">
        @foreach($partners as $pharmacy)
            <article class="ep-card">
                <img src="{{ asset('assets/images/photos/pharmacie.jpg') }}" alt=""
                     style="width:100%;height:96px;object-fit:cover;border-bottom:1px solid var(--ep-rule-soft)" loading="lazy">
                <div class="ep-card__body">
                    <p style="font-size:.9375rem;font-weight:650;margin:0">{{ $pharmacy->name }}</p>
                    <p class="ep-small" style="margin:.1875rem 0 0">{{ $pharmacy->area_label }}</p>
                    <p class="ep-mono ep-small" style="display:flex;align-items:center;gap:.5rem;margin:.75rem 0 0;color:var(--ep-green-dark)">
                        <span style="width:6px;height:6px;border-radius:50%;background:var(--ep-green)"></span>
                        {{ $pharmacy->response_label }}
                    </p>
                </div>
            </article>
        @endforeach
    </div>
</section>
@endsection
