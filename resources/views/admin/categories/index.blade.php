@extends('layouts.admin')

@section('title', 'Catégories — ePharma')
@section('page_title', 'Catégories')
@section('page_subtitle', 'Comment le catalogue est rangé pour le client')

@section('content')
    <div class="ep-split">
        <x-ep.card flush style="order:2">
            <header class="ep-card__head">
                <h2 class="ep-card__title">{{ $categories->total() }} catégorie(s)</h2>
                <form method="GET" class="ep-row ep-spacer" style="gap:.375rem">
                    <label class="ep-sr-only" for="q">Nom</label>
                    <input class="ep-input" id="q" name="q" value="{{ $q }}" placeholder="Nom…"
                           style="width:160px;padding:.5rem .75rem;font-size:.8125rem">
                    <button type="submit" class="ep-btn ep-btn--ghost ep-btn--sm">Filtrer</button>
                </form>
            </header>

            @if($categories->isEmpty())
                <x-ep.empty title="Aucune catégorie."
                            text="Créez-en une pour ranger les médicaments du référentiel." />
            @else
                <div class="ep-table-wrap">
                    <table class="ep-table ep-table--cards">
                        <thead>
                            <tr>
                                <th scope="col">Catégorie</th>
                                <th scope="col">Identifiant</th>
                                <th scope="col">État</th>
                                <th scope="col">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($categories as $category)
                                <tr>
                                    <td data-label="Catégorie" class="ep-cell-name">
                                        <span style="font-size:.84375rem;font-weight:650">{{ $category->name }}</span>
                                    </td>
                                    <td data-label="Identifiant"><span class="ep-mono ep-small">{{ $category->slug }}</span></td>
                                    <td data-label="État">
                                        <x-ep.badge :tone="$category->is_active ? 'green' : 'neutral'"
                                                    :label="$category->is_active ? 'Active' : 'Désactivée'" />
                                    </td>
                                    <td data-label="Action">
                                        <div class="ep-cell-actions">
                                            <a href="{{ route('manager.categories.edit', $category) }}" class="ep-btn ep-btn--ghost ep-btn--sm">Modifier</a>
                                            @if($category->is_active)
                                                <form method="POST" action="{{ route('manager.categories.destroy', $category) }}">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="ep-btn ep-btn--danger-soft ep-btn--sm">Désactiver</button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            @if($categories->hasPages())
                <x-slot:footer>{{ $categories->links() }}</x-slot:footer>
            @endif
        </x-ep.card>

        <x-ep.card title="Nouvelle catégorie" style="order:1">
            <form method="POST" action="{{ route('manager.categories.store') }}">
                @csrf
                <div class="ep-field">
                    <label class="ep-label" for="name">Nom</label>
                    <input class="ep-input" id="name" name="name" required maxlength="255"
                           value="{{ old('name') }}" placeholder="Douleur et fièvre">
                    <p class="ep-hint">L'identifiant est généré automatiquement.</p>
                    <x-input-error :messages="$errors->get('name')" class="ep-error" />
                </div>

                <label class="ep-choice" style="margin-top:.875rem">
                    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', true))>
                    Visible dans le catalogue client
                </label>

                <button type="submit" class="ep-btn ep-btn--primary ep-btn--block" style="margin-top:1rem">
                    Créer la catégorie
                </button>
            </form>
        </x-ep.card>
    </div>
@endsection
