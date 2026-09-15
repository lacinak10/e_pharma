@extends('layouts.admin')

@section('title', 'Paramètres — ePharma')
@section('page_title', 'Paramètres')

@php $isManager = auth()->user()->isManager(); @endphp

@section('content')
    <div class="ep-split">
        <form method="POST" action="{{ route('admin.settings.update') }}">
            @csrf @method('PUT')

            @if($isManager)
            <x-ep.card title="Informations de l'entreprise">
                <p class="ep-small" style="margin:0 0 1.25rem">
                    Ces coordonnées sont <strong>publiques</strong> : elles s'affichent dans la barre
                    du haut, le pied de page et la fiche de chaque médicament. Toute modification est
                    visible par les clients dès l'enregistrement.
                </p>

                <div class="ep-grid" style="grid-template-columns:repeat(auto-fit,minmax(min(100%,220px),1fr));gap:1rem">
                    <div class="ep-field">
                        <label class="ep-label" for="name">Nom commercial</label>
                        <input class="ep-input" id="name" name="name" required maxlength="80"
                               value="{{ old('name', $company['name']) }}">
                        <x-input-error :messages="$errors->get('name')" class="ep-error" />
                    </div>

                    <div class="ep-field">
                        <label class="ep-label" for="phone">Téléphone</label>
                        <input class="ep-input" id="phone" name="phone" type="tel" required maxlength="40"
                               value="{{ old('phone', $company['phone']) }}" placeholder="+225 27 22 00 00 00">
                        <p class="ep-hint">Affiché tel quel, et rendu appelable d'un toucher.</p>
                        <x-input-error :messages="$errors->get('phone')" class="ep-error" />
                    </div>

                    <div class="ep-field">
                        <label class="ep-label" for="email">E-mail de contact</label>
                        <input class="ep-input" id="email" name="email" type="email" required maxlength="120"
                               value="{{ old('email', $company['email']) }}" placeholder="contact@epharma.ci">
                        <x-input-error :messages="$errors->get('email')" class="ep-error" />
                    </div>

                    <div class="ep-field">
                        <label class="ep-label" for="address">Adresse</label>
                        <input class="ep-input" id="address" name="address" maxlength="160"
                               value="{{ old('address', $company['address']) }}" placeholder="Cocody, Abidjan">
                        <x-input-error :messages="$errors->get('address')" class="ep-error" />
                    </div>

                    <div class="ep-field">
                        <label class="ep-label" for="city">Ville et pays</label>
                        <input class="ep-input" id="city" name="city" maxlength="80"
                               value="{{ old('city', $company['city']) }}" placeholder="Abidjan · Côte d'Ivoire">
                        <p class="ep-hint">Repris en bas de chaque page de la boutique.</p>
                        <x-input-error :messages="$errors->get('city')" class="ep-error" />
                    </div>
                </div>

                <p class="ep-hint" style="margin:1rem 0 0">
                    Un champ facultatif laissé vide retrouve sa valeur par défaut :
                    la boutique n'affiche jamais un contact vide.
                </p>
            </x-ep.card>
            @endif

            <x-ep.card title="Préférences" :style="$isManager ? 'margin-top:1.25rem' : null">
                <div class="ep-field">
                    <label class="ep-label" for="language">Langue de l'interface</label>
                    <select class="ep-select" id="language" name="language" required>
                        <option value="fr" @selected(($settings['language'] ?? 'fr') === 'fr')>Français</option>
                        <option value="en" @selected(($settings['language'] ?? 'fr') === 'en')>English</option>
                    </select>
                    <x-input-error :messages="$errors->get('language')" class="ep-error" />
                </div>

                <label class="ep-choice" style="margin-top:1rem">
                    <input type="checkbox" name="notifications" value="1" @checked($settings['notifications'] ?? true)>
                    <span>
                        <strong>Recevoir les notifications du back-office</strong><br>
                        <span class="ep-small">Nouvelles commandes, verdicts de disponibilité, étapes de livraison.</span>
                    </span>
                </label>

                <x-slot:footer>
                    <button type="submit" class="ep-btn ep-btn--primary">Enregistrer</button>
                </x-slot:footer>
            </x-ep.card>
        </form>

        <div>
            @if($isManager)
            <x-ep.card title="Ce que voit le client">
                <p class="ep-small" style="margin:0 0 .875rem">
                    Aperçu des coordonnées actuellement publiées.
                </p>

                <dl style="margin:0;display:flex;flex-direction:column;gap:.75rem">
                    <div>
                        <dt class="ep-label">Barre du haut et fiche médicament</dt>
                        <dd style="margin:.1875rem 0 0">
                            <a class="ep-mono ep-small" href="tel:{{ $company['phone_href'] }}">{{ $company['phone'] }}</a>
                        </dd>
                    </div>
                    <div>
                        <dt class="ep-label">Pied de page</dt>
                        <dd class="ep-small" style="margin:.1875rem 0 0">
                            <a href="mailto:{{ $company['email'] }}">{{ $company['email'] }}</a>
                        </dd>
                    </div>
                    <div>
                        <dt class="ep-label">Adresse</dt>
                        <dd class="ep-small" style="margin:.1875rem 0 0">{{ $company['address'] }}</dd>
                    </div>
                    <div>
                        <dt class="ep-label">Bas de page</dt>
                        <dd class="ep-mono ep-small" style="margin:.1875rem 0 0">{{ $company['city'] }}</dd>
                    </div>
                </dl>
            </x-ep.card>
            @endif

            <x-ep.card title="Le chrono de vérification" :style="$isManager ? 'margin-top:1.25rem' : null">
                <p class="ep-small" style="margin:0 0 1rem">
                    ePharma annonce au client un résultat de disponibilité
                    <strong>en moins de {{ (int) (\App\Models\Order::CHECK_DURATION_SECONDS / 60) }} minutes</strong>.
                    Ce délai est au cœur de la promesse produit : il est défini dans
                    <span class="ep-mono">App\Models\Order::CHECK_DURATION_SECONDS</span>.
                </p>
                <p class="ep-hint" style="margin:0">
                    Le modifier change le compte à rebours affiché au client comme au manager.
                </p>
            </x-ep.card>
        </div>
    </div>
@endsection
