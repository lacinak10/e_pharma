@extends('layouts.store')

@section('title', 'Comment ça marche — ePharma')

@section('content')
    <section class="ep-shell ep-section">
        <p class="ep-eyebrow ep-eyebrow--green">Comment ça marche</p>
        <h1 class="ep-h1" style="max-width:22ch;margin-top:.75rem">
            Quatre étapes, et vous n'avez pas bougé de chez vous.
        </h1>
        <p class="ep-lead" style="max-width:62ch;margin-top:1.25rem">
            ePharma existe pour une raison simple : trop de patients se déplacent en pharmacie pour découvrir
            sur place que leur médicament n'est pas disponible. Vous commandez, nous appelons nos pharmacies
            partenaires, et vous savez en moins de 5 minutes si votre médicament est là.
        </p>
    </section>

    <section class="ep-shell" style="padding-bottom:clamp(2.5rem,1.5rem + 4vw,5rem)">
        <x-store.journey />
    </section>

    <section style="background:var(--ep-surface);border-block:1px solid var(--ep-rule)">
        <div class="ep-shell ep-section">
            <div class="ep-grid ep-grid--2">
                <div>
                    <h2 class="ep-h3">Combien de temps prend la vérification ?</h2>
                    <p class="ep-body" style="margin-top:.75rem">
                        Moins de 5 minutes dans la très grande majorité des cas. Le délai affiché diminue en direct
                        pendant le traitement, et vous êtes notifié dès que le résultat est connu — disponible,
                        partiellement disponible ou indisponible avec des alternatives.
                    </p>

                    <h2 class="ep-h3" style="margin-top:2rem">Médicaments sur ordonnance</h2>
                    <p class="ep-body" style="margin-top:.75rem">
                        Certains médicaments ne peuvent être délivrés que sur présentation d'une ordonnance.
                        Vous la téléversez au moment de la commande et précisez si vous souhaitez
                        <strong>tous les médicaments</strong> ou <strong>seulement une partie</strong>,
                        en indiquant lesquels.
                    </p>
                </div>

                <div>
                    <h2 class="ep-h3">Suivi et notation</h2>
                    <p class="ep-body" style="margin-top:.75rem">
                        Dès qu'un livreur est assigné, vous recevez son nom, son numéro et le délai estimé.
                        Puis les cinq étapes s'allument une à une : en route vers la pharmacie, arrivé à la
                        pharmacie, médicament récupéré, en route vers vous, commande livrée.
                    </p>
                    <p class="ep-body" style="margin-top:.75rem">
                        Après réception, vous notez votre livreur sur 5 et laissez un commentaire.
                    </p>

                    <div class="ep-row" style="margin-top:1.5rem">
                        <a href="{{ route('store.medicines.index') }}" class="ep-btn ep-btn--primary">Voir tous les médicaments</a>
                        <a href="{{ route('store.prescriptions.create') }}" class="ep-btn ep-btn--ghost">J'ai une ordonnance</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="ep-shell ep-section">
        <h2 class="ep-h3" style="margin-bottom:1.5rem">Les 14 étapes du cycle de vie d'une commande</h2>
        <div class="ep-card ep-table-wrap">
            <table class="ep-table ep-table--cards">
                <thead>
                    <tr>
                        <th scope="col">N°</th>
                        <th scope="col">Statut</th>
                        <th scope="col">Ce que vous voyez</th>
                        <th scope="col">Qui agit</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach(\App\Enums\OrderStatus::lifecycle() as $status)
                        <tr>
                            <td data-label="N°"><span class="ep-cell-num">{{ $status->number() }}</span></td>
                            <td data-label="Statut"><x-ep.badge :status="$status" /></td>
                            <td data-label="Ce que vous voyez"><span class="ep-small" style="color:var(--ep-text-2)">{{ $status->label() }}</span></td>
                            <td data-label="Qui agit"><span class="ep-mono ep-small">{{ $status->actor() }}</span></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>
@endsection
