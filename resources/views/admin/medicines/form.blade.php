@extends('layouts.admin')

@php $isNew = ! $medicine->exists; @endphp

@section('title', ($isNew ? 'Nouveau médicament' : $medicine->name) . ' — ePharma')
@section('page_title', $isNew ? 'Ajouter au référentiel' : 'Modifier ' . $medicine->name)
@section('page_subtitle', "Le catalogue décrit ce que l'on sait commander, pas un stock détenu")

@section('content')
    <form method="POST" enctype="multipart/form-data" style="max-width:820px"
          action="{{ $isNew ? route('manager.medicines.store') : route('manager.medicines.update', $medicine) }}">
        @csrf
        @unless($isNew) @method('PUT') @endunless

        <x-ep.card>
            <div class="ep-grid" style="grid-template-columns:repeat(auto-fit,minmax(min(100%,220px),1fr));gap:1rem">
                <div class="ep-field">
                    <label class="ep-label" for="name">Nom</label>
                    <input class="ep-input" id="name" name="name" required maxlength="255"
                           value="{{ old('name', $medicine->name) }}" placeholder="Doliprane Tabs 1000 mg">
                    <x-input-error :messages="$errors->get('name')" class="ep-error" />
                </div>

                <div class="ep-field">
                    <label class="ep-label" for="category_id">Catégorie</label>
                    <select class="ep-select" id="category_id" name="category_id" required>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" @selected(old('category_id', $medicine->category_id) == $category->id)>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('category_id')" class="ep-error" />
                </div>

                <div class="ep-field">
                    <label class="ep-label" for="indication">Indication</label>
                    <input class="ep-input" id="indication" name="indication" maxlength="255"
                           value="{{ old('indication', $medicine->indication) }}"
                           placeholder="Douleur et fièvre — dès 50 kg">
                    <p class="ep-hint">Affichée sous le nom sur la carte produit.</p>
                </div>

                <div class="ep-field">
                    <label class="ep-label" for="dosage">Dosage</label>
                    <input class="ep-input ep-mono" id="dosage" name="dosage" maxlength="60"
                           value="{{ old('dosage', $medicine->dosage) }}" placeholder="1000 mg">
                </div>

                <div class="ep-field">
                    <label class="ep-label" for="pack">Conditionnement</label>
                    <input class="ep-input" id="pack" name="pack" maxlength="60"
                           value="{{ old('pack', $medicine->pack) }}" placeholder="8 comprimés">
                </div>

                <div class="ep-field">
                    <label class="ep-label" for="price">Prix indicatif (FCFA)</label>
                    <input class="ep-input ep-mono" id="price" name="price" type="number" min="0" required
                           value="{{ old('price', $medicine->price) }}">
                    <p class="ep-hint">Confirmé par la pharmacie lors de la vérification.</p>
                    <x-input-error :messages="$errors->get('price')" class="ep-error" />
                </div>

                <div class="ep-field">
                    <label class="ep-label" for="reference">Référence <span class="ep-hint">(facultatif)</span></label>
                    <input class="ep-input ep-mono" id="reference" name="reference" maxlength="100"
                           value="{{ old('reference', $medicine->reference) }}" placeholder="générée automatiquement">
                    <x-input-error :messages="$errors->get('reference')" class="ep-error" />
                </div>

                <div class="ep-field">
                    <label class="ep-label" for="image">Packshot</label>
                    <input class="ep-input" id="image" name="image" type="file" accept="image/*">
                    <p class="ep-hint">À défaut, un visuel générique est utilisé.</p>
                    <x-input-error :messages="$errors->get('image')" class="ep-error" />
                </div>
            </div>

            <div class="ep-field" style="margin-top:1rem">
                <label class="ep-label" for="description">Description</label>
                <textarea class="ep-textarea" id="description" name="description" maxlength="2000">{{ old('description', $medicine->description) }}</textarea>
            </div>

            <div class="ep-stack" style="gap:.5rem;margin-top:1.25rem">
                <label class="ep-choice">
                    <input type="checkbox" name="requires_prescription" value="1"
                           @checked(old('requires_prescription', $medicine->requires_prescription))>
                    Délivrance sur ordonnance obligatoire
                </label>
                <label class="ep-choice">
                    <input type="checkbox" name="is_active" value="1"
                           @checked(old('is_active', $medicine->is_active ?? true))>
                    Référencé au catalogue client
                </label>
                @if(! $isNew && $medicine->image_url)
                    <label class="ep-choice">
                        <input type="checkbox" name="remove_image" value="1">
                        Supprimer le packshot actuel
                    </label>
                @endif
            </div>

            <x-slot:footer>
                <div class="ep-row">
                    <button type="submit" class="ep-btn ep-btn--primary">{{ $isNew ? 'Ajouter au référentiel' : 'Enregistrer' }}</button>
                    <a href="{{ route('manager.medicines.index') }}" class="ep-btn ep-btn--ghost">Annuler</a>
                </div>
            </x-slot:footer>
        </x-ep.card>
    </form>
@endsection
