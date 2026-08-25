@extends('layouts.store')

@section('title', ($q ? "« {$q} » — " : '') . 'Médicaments — ePharma')

@section('content')
<div class="ep-shell ep-section--tight">

    <div style="margin-bottom:1.75rem">
        <h1 class="ep-h2">
            @if($q) Résultats pour « {{ $q }} » @else Tous les médicaments @endif
        </h1>
        <p class="ep-small" style="margin-top:.5rem">
            {{ $medicines->total() }} médicament{{ $medicines->total() > 1 ? 's' : '' }} ·
            disponibilité confirmée auprès de nos partenaires avant chaque livraison
        </p>
    </div>

    <div class="ep-split">
        {{-- Filtres --}}
        <aside class="ep-stack" style="order:2">
            <x-ep.card title="Catégories" flush>
                <nav style="padding:.5rem 0">
                    <a href="{{ route('store.medicines.index', array_filter(['q' => $q])) }}"
                       class="ep-nav__link" style="color:var(--ep-text-3);border-left-color:{{ $category ? 'transparent' : 'var(--ep-green)' }}">
                        <span class="ep-nav__label">Toutes les catégories</span>
                    </a>
                    @foreach($categories as $cat)
                        <a href="{{ route('store.medicines.index', array_filter(['q' => $q, 'category' => $cat->slug])) }}"
                           class="ep-nav__link"
                           style="color:{{ $category === $cat->slug ? 'var(--ep-green-dark)' : 'var(--ep-text-3)' }};
                                  font-weight:{{ $category === $cat->slug ? 650 : 500 }};
                                  border-left-color:{{ $category === $cat->slug ? 'var(--ep-green)' : 'transparent' }}">
                            <span class="ep-nav__label">{{ $cat->name }}</span>
                        </a>
                    @endforeach
                </nav>
            </x-ep.card>

            <x-ep.card>
                <p class="ep-eyebrow" style="color:var(--ep-red)">Sur ordonnance</p>
                <p class="ep-small" style="margin:.75rem 0 1rem">
                    Certains médicaments exigent une ordonnance téléversée au moment de la commande.
                </p>
                <a href="{{ route('store.prescriptions.create') }}" class="ep-btn ep-btn--ghost ep-btn--block">
                    Envoyer mon ordonnance
                </a>
            </x-ep.card>
        </aside>

        {{-- Résultats --}}
        <div style="order:1">
            @if($medicines->isEmpty())
                <x-ep.card>
                    <x-ep.empty
                        title="Aucun médicament ne correspond."
                        text="Essayez un autre nom, une molécule, ou envoyez-nous directement votre ordonnance.">
                        <x-slot:actions>
                            <a href="{{ route('store.medicines.index') }}" class="ep-btn ep-btn--ghost">Voir tout le catalogue</a>
                            <a href="{{ route('store.prescriptions.create') }}" class="ep-btn ep-btn--primary">Envoyer une ordonnance</a>
                        </x-slot:actions>
                    </x-ep.empty>
                </x-ep.card>
            @else
                <div class="ep-grid ep-grid--cards">
                    @foreach($medicines as $medicine)
                        <x-ep.product-card :medicine="$medicine" />
                    @endforeach
                </div>

                @if($medicines->hasPages())
                    <div style="margin-top:2rem">{{ $medicines->links() }}</div>
                @endif
            @endif
        </div>
    </div>
</div>
@endsection
