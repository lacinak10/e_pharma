@extends('layouts.admin')

@section('title', 'Paramètres — ePharma')
@section('page_title', 'Paramètres')

@section('content')
    <div class="ep-split">
        <form method="POST" action="{{ route('admin.settings.update') }}">
            @csrf @method('PUT')

            <x-ep.card title="Préférences">
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

        <x-ep.card title="Le chrono de vérification">
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
@endsection
