@extends('layouts.admin')

@section('title', 'E-PHARMA - Gestion des Produits')
@section('page_title', 'Gestion des produits')

@section('content')
@php
    /**
     * Données attendues depuis le controller :
     * - $medicines : LengthAwarePaginator<Medicine>
     * - $categories : array<string> (optionnel)
     * - $filters : ['q' => ?, 'category' => ?, 'status' => ?]
     *
     * Pour pouvoir tester sans controller, tu peux commenter ce bloc @php et passer des données réelles.
     */
    $filters = $filters ?? [
        'q' => request('q'),
        'category' => request('category'),
        'status' => request('status'),
    ];

    $categories = $categories ?? ['Antidouleur','Antibiotique','Vitamines','Soins de la peau'];

    // Demo fallback si $medicines n'existe pas (à enlever dès que tu connectes au controller)
    if (!isset($medicines)) {
        $demo = collect([
            (object)['id'=>1,'name'=>'Paracétamol 500mg','sku'=>'MED-001','category'=>'Antidouleur','price'=>1500,'stock'=>45,'image_url'=>'https://picsum.photos/40?random=2'],
            (object)['id'=>2,'name'=>'Amoxicilline 500mg','sku'=>'MED-002','category'=>'Antibiotique','price'=>3200,'stock'=>12,'image_url'=>'https://picsum.photos/40?random=3'],
            (object)['id'=>3,'name'=>'Vitamine C 500mg','sku'=>'MED-003','category'=>'Vitamines','price'=>2500,'stock'=>0,'image_url'=>'https://picsum.photos/40?random=4'],
            (object)['id'=>4,'name'=>'Crème hydratante','sku'=>'COS-001','category'=>'Soins de la peau','price'=>4800,'stock'=>22,'image_url'=>'https://picsum.photos/40?random=5'],
        ]);

        $medicines = new \Illuminate\Pagination\LengthAwarePaginator(
            $demo, $demo->count(), 10, 1, ['path' => request()->url(), 'query' => request()->query()]
        );
    }

    $fmt = fn(int $amount) => number_format($amount, 0, ',', ' ') . ' FCFA';

    // helper status
    $stockStatus = function($m) {
        // règle simple : 0 => épuisé, <= 15 => faible, sinon stock
        if ((int)$m->stock <= 0) return ['label' => 'Épuisé', 'variant' => 'red'];
        if ((int)$m->stock <= 15) return ['label' => 'Stock faible', 'variant' => 'yellow'];
        return ['label' => 'En stock', 'variant' => 'green'];
    };
@endphp

{{-- Barre recherche + filtres --}}
<x-admin.filter-bar
    action="{{ url('/admin/medicines') }}"
    :q="$filters['q']"
    :category="$filters['category']"
    :status="$filters['status']"
    :categories="$categories"
/>

{{-- Table + actions --}}
<x-admin.card title="Liste des produits" class="p-0">
    <x-slot:actions>
        <div class="flex space-x-2">
            <x-admin.button
                type="button"
                variant="primary"
                icon="fas fa-plus"
                data-modal-open="addMedicineModal"
            >
                Ajouter un produit
            </x-admin.button>

            <x-admin.button type="button" variant="outline" icon="fas fa-file-export">
                Exporter
            </x-admin.button>
        </div>
    </x-slot:actions>

    <x-admin.table>
        <x-slot:head>
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    <input type="checkbox" class="h-4 w-4 text-primary focus:ring-primary border-gray-300 rounded">
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Produit</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Catégorie</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Prix</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stock</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Statut</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
            </tr>
        </x-slot:head>

        @forelse($medicines as $m)
            @php $st = $stockStatus($m); @endphp

            <x-admin.medicine-row
                :medicine="$m"
                :price="$fmt((int)$m->price)"
                :statusLabel="$st['label']"
                :statusVariant="$st['variant']"
                editUrl="{{ url('/admin/medicines/'.$m->id.'/edit') }}"
                deleteUrl="{{ url('/admin/medicines/'.$m->id) }}"
            />
        @empty
            <tr>
                <td colspan="7" class="px-6 py-10">
                    <x-admin.empty-state
                        title="Aucun produit"
                        description="Aucun produit ne correspond à tes filtres."
                        icon="fas fa-pills"
                    />
                </td>
            </tr>
        @endforelse
    </x-admin.table>

    <x-admin.pagination :paginator="$medicines" />
</x-admin.card>
@endsection

@push('modals')
    {{-- Modal création produit (UI only, relie ensuite au vrai POST) --}}
    <x-admin.modal id="addMedicineModal" title="Ajouter un nouveau produit">
        <form method="POST" action="{{ route('manager.medicines.store') }}" class="space-y-4" enctype="multipart/form-data">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nom du produit</label>
                    <x-admin.input  name="name" placeholder="Ex: Paracétamol 500mg" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Référence (SKU)</label>
                    <x-admin.input name="reference" placeholder="Ex: MED-001" />
                </div>
            </div>



            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Catégorie</label>
                    <x-admin.select name="category_id">
                        <option value="">Sélectionner une catégorie</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->name }}">{{ $cat->name }}</option>
                        @endforeach
                    </x-admin.select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Prix (FCFA)</label>
                    <x-admin.input name="price" type="number" min="0" placeholder="Ex: 1500" />
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Quantité en stock</label>
                    <x-admin.input name="stock" type="number" min="0" placeholder="Ex: 50" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Seuil d'alerte</label>
                    <x-admin.input name="alert_threshold" type="number" min="0" placeholder="Ex: 15" />
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                <textarea name="description" rows="3"
                          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary"></textarea>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Image du produit</label>
                <x-admin.image-upload name="image_url" />
            </div>



 <div class="pt-4 border-t border-gray-200 flex justify-end space-x-3">
            <x-admin.button type="button" variant="outline" data-modal-close="addMedicineModal">
                Annuler
            </x-admin.button>

            <x-admin.button type="submit" variant="primary">
                Enregistrer
            </x-admin.button>
        </div>
        </form>


    </x-admin.modal>
@endpush
