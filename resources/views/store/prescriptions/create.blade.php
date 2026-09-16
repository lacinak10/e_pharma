@extends('layouts.store')

@section('title', 'Envoyer mon ordonnance — ePharma')

@section('content')
<div class="ep-shell ep-section--tight">

    <div style="max-width:56ch;margin-bottom:2rem">
        <p class="ep-eyebrow ep-eyebrow--green">Ordonnance</p>
        <h1 class="ep-h2" style="margin:.75rem 0 1rem">Envoyez votre ordonnance en une photo.</h1>
        <p class="ep-lead">
            Le manager la reçoit avec votre commentaire, contacte nos pharmacies partenaires,
            et vous savez en moins de 5 minutes ce qui est disponible.
        </p>
    </div>

    <form method="POST" action="{{ route('store.prescriptions.store') }}" enctype="multipart/form-data">
        @csrf

        <div class="ep-split">
            <div class="ep-stack">

                {{-- 1. Le document --}}
                <x-ep.card title="1. Votre ordonnance">
                    <div class="ep-field">
                        <label class="ep-label" for="prescription">Photo ou PDF de l'ordonnance</label>
                        <input class="ep-input" type="file" id="prescription" name="prescription"
                               accept="image/jpeg,image/png,application/pdf" required
                               data-ep-preview-input>
                        <p class="ep-hint">
                            JPG, PNG ou PDF, 4 Mo maximum. Prenez la photo à plat, à la lumière du jour :
                            une photo floue ne peut pas être lue.
                        </p>
                        <x-input-error :messages="$errors->get('prescription')" class="ep-error" />
                    </div>

                    <div class="ep-viewer__stage" style="margin-top:1rem;border-radius:var(--ep-radius);border:1px dashed var(--ep-border-input)"
                         data-ep-preview-stage hidden>
                        <img alt="Aperçu de votre ordonnance" data-ep-preview-image>
                    </div>
                </x-ep.card>

                {{-- 2. Le périmètre demandé --}}
                <x-ep.card title="2. Que souhaitez-vous exactement ?">
                    <fieldset style="border:0;padding:0;margin:0">
                        <legend class="ep-sr-only">Périmètre de la commande</legend>

                        <div class="ep-stack" style="gap:.5rem">
                            <label class="ep-choice">
                                <input type="radio" name="prescription_scope" value="all"
                                       @checked(old('prescription_scope', 'all') === 'all')
                                       data-ep-scope>
                                <span>
                                    <strong>Tous les médicaments de l'ordonnance</strong><br>
                                    <span class="ep-small">Nous vérifions la disponibilité de chaque ligne.</span>
                                </span>
                            </label>

                            <label class="ep-choice">
                                <input type="radio" name="prescription_scope" value="partial"
                                       @checked(old('prescription_scope') === 'partial')
                                       data-ep-scope>
                                <span>
                                    <strong>Seulement une partie</strong><br>
                                    <span class="ep-small">Indiquez précisément lesquels ci-dessous.</span>
                                </span>
                            </label>
                        </div>
                        <x-input-error :messages="$errors->get('prescription_scope')" class="ep-error" />
                    </fieldset>

                    <div class="ep-field" style="margin-top:1rem">
                        <label class="ep-label" for="prescription_comment">Votre commentaire au manager</label>
                        <textarea class="ep-textarea" id="prescription_comment" name="prescription_comment"
                                  maxlength="1000"
                                  placeholder="Ex. : uniquement l'Amoxicilline et le sirop, j'ai déjà le Paracétamol."
                        >{{ old('prescription_comment') }}</textarea>
                        <x-input-error :messages="$errors->get('prescription_comment')" class="ep-error" />
                    </div>
                </x-ep.card>

                {{-- 3. La livraison --}}
                <x-ep.card title="3. Livraison">
                    <div class="ep-grid" style="grid-template-columns:repeat(auto-fit,minmax(min(100%,240px),1fr))">
                        <div class="ep-field">
                            <label class="ep-label" for="delivery_address">Adresse de livraison</label>
                            <input class="ep-input" id="delivery_address" name="delivery_address" required
                                   minlength="5" maxlength="255"
                                   value="{{ old('delivery_address', auth()->user()->zone ? auth()->user()->zone . ', Abidjan' : '') }}"
                                   placeholder="Cocody Angré, rue des Jardins">
                            <x-input-error :messages="$errors->get('delivery_address')" class="ep-error" />
                        </div>

                        <div class="ep-field">
                            <label class="ep-label" for="delivery_phone">Téléphone</label>
                            <input class="ep-input ep-mono" id="delivery_phone" name="delivery_phone" required
                                   value="{{ old('delivery_phone', auth()->user()->phone) }}"
                                   placeholder="+2250700000000">
                            <p class="ep-hint">Le livreur vous appellera avant d'arriver.</p>
                            <x-input-error :messages="$errors->get('delivery_phone')" class="ep-error" />
                        </div>
                    </div>

                    <div class="ep-field" style="margin-top:1rem">
                        <label class="ep-label" for="notes">Précisions pour le livreur <span class="ep-hint">(facultatif)</span></label>
                        <textarea class="ep-textarea" id="notes" name="notes" maxlength="500"
                                  placeholder="Étage, point de repère, horaire…">{{ old('notes') }}</textarea>
                        <x-input-error :messages="$errors->get('notes')" class="ep-error" />
                    </div>
                </x-ep.card>

                {{-- 4. Le paiement --}}
                <x-ep.card title="4. Paiement">
                    <fieldset style="border:0;padding:0;margin:0">
                        <legend class="ep-sr-only">Moyen de paiement</legend>
                        <div class="ep-grid" style="grid-template-columns:repeat(auto-fit,minmax(min(100%,160px),1fr));gap:.5rem">
                            @foreach(['cash' => 'Espèces à la remise', 'momo' => 'Mobile Money', 'card' => 'Carte bancaire'] as $value => $label)
                                <label class="ep-choice">
                                    <input type="radio" name="payment_method" value="{{ $value }}"
                                           @checked(old('payment_method', 'cash') === $value)>
                                    {{ $label }}
                                </label>
                            @endforeach
                        </div>
                        <x-input-error :messages="$errors->get('payment_method')" class="ep-error" />
                    </fieldset>

                    <p class="ep-hint" style="margin-top:1rem">
                        Rien n'est débité maintenant : le manager compose d'abord votre panier
                        d'après l'ordonnance, puis vous réglez le montant confirmé.
                    </p>
                </x-ep.card>
            </div>

            <div class="ep-stack">
                <x-ep.card title="Ce qui va se passer">
                    <ol class="ep-timeline" style="list-style:none;margin:0;padding:0">
                        @foreach([
                            ['Votre ordonnance nous parvient', 'Le manager la lit avec votre commentaire.'],
                            ['Vérification auprès des partenaires', 'Résultat annoncé en moins de 5 minutes.'],
                            ['Verdict de disponibilité', 'Disponible, partiel ou indisponible avec alternatives.'],
                            ['Un livreur récupère', 'Vous recevez son nom, son numéro et le délai.'],
                            ['Livraison et notation', 'Vous notez le livreur sur 5.'],
                        ] as $i => [$label, $sub])
                            <li class="ep-timeline__step ep-timeline__step--{{ $i === 0 ? 'current' : 'todo' }}">
                                <span class="ep-timeline__rail" aria-hidden="true">
                                    <span class="ep-timeline__dot"></span>
                                    <span class="ep-timeline__line"></span>
                                </span>
                                <div class="ep-timeline__content">
                                    <p class="ep-timeline__label">{{ $label }}</p>
                                    <p class="ep-timeline__sub">{{ $sub }}</p>
                                </div>
                            </li>
                        @endforeach
                    </ol>

                    <x-slot:footer>
                        <button type="submit" class="ep-btn ep-btn--primary ep-btn--block">Envoyer mon ordonnance</button>
                        <p class="ep-hint" style="text-align:center;margin-top:.75rem">
                            Les médicaments ne sont pas des produits ordinaires — demandez conseil à un pharmacien.
                        </p>
                    </x-slot:footer>
                </x-ep.card>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    // Aperçu immédiat de l'ordonnance : le client voit tout de suite si sa photo est lisible.
    document.addEventListener('DOMContentLoaded', () => {
        const input = document.querySelector('[data-ep-preview-input]');
        const stage = document.querySelector('[data-ep-preview-stage]');
        const image = document.querySelector('[data-ep-preview-image]');
        const comment = document.getElementById('prescription_comment');

        input?.addEventListener('change', () => {
            const file = input.files?.[0];
            if (!file || !file.type.startsWith('image/')) { stage.hidden = true; return; }
            image.src = URL.createObjectURL(file);
            stage.hidden = false;
        });

        // Le commentaire devient obligatoire quand la commande est partielle.
        document.querySelectorAll('[data-ep-scope]').forEach(radio => {
            radio.addEventListener('change', () => {
                const partial = document.querySelector('[data-ep-scope][value="partial"]').checked;
                comment.required = partial;
                comment.placeholder = partial
                    ? "Indiquez précisément les médicaments souhaités."
                    : "Une précision pour le manager ? (facultatif)";
            });
        });
    });
</script>
@endpush
