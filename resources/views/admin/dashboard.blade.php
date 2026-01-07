@extends('layouts.admin')

@section('title', 'E-PHARMA - Tableau de Bord Pharmacie')
@section('page_title', 'Tableau de bord')

@section('content')
@php
    // Démo: remplace par tes données venant du controller
    $stats = [
        ['label' => "Commandes aujourd'hui", 'value' => 24, 'icon' => 'fas fa-shopping-cart', 'valueClass' => 'text-primary', 'iconWrapClass' => 'bg-blue-100 text-primary'],
        ['label' => "Produits en stock", 'value' => 156, 'icon' => 'fas fa-pills', 'valueClass' => 'text-accent', 'iconWrapClass' => 'bg-green-100 text-accent'],
        ['label' => "Produits épuisés", 'value' => 8, 'icon' => 'fas fa-exclamation-circle', 'valueClass' => 'text-danger', 'iconWrapClass' => 'bg-red-100 text-danger'],
        ['label' => "Statut", 'value' => 'Ouvert', 'icon' => 'fas fa-store', 'valueClass' => 'text-secondary', 'iconWrapClass' => 'bg-indigo-100 text-secondary'],
    ];

    $recentOrders = [
        ['id' => '#EP-1001', 'customer' => 'Jean Dupont', 'date' => '10/05/2023', 'amount' => '25 000 FCFA', 'status' => 'Livré', 'badge' => 'green'],
        ['id' => '#EP-1002', 'customer' => 'Marie Koné', 'date' => '10/05/2023', 'amount' => '18 500 FCFA', 'status' => 'En cours', 'badge' => 'yellow'],
        ['id' => '#EP-1003', 'customer' => 'Paul Yao', 'date' => '09/05/2023', 'amount' => '32 000 FCFA', 'status' => 'Préparation', 'badge' => 'blue'],
    ];

    $products = [
        ['image' => 'https://picsum.photos/40?random=2', 'name' => 'Paracétamol 500mg', 'category' => 'Antidouleur', 'price' => '1 500 FCFA', 'stock' => 45],
        ['image' => 'https://picsum.photos/40?random=3', 'name' => 'Amoxicilline 500mg', 'category' => 'Antibiotique', 'price' => '3 200 FCFA', 'stock' => 12],
    ];
@endphp

{{-- Stats cards --}}
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    @foreach($stats as $s)
        <x-admin.stat-card
            :label="$s['label']"
            :value="$s['value']"
            :icon="$s['icon']"
            :valueClass="$s['valueClass']"
            :iconWrapClass="$s['iconWrapClass']"
        />
    @endforeach
</div>

{{-- Recent orders --}}
<x-admin.card title="Commandes récentes" class="p-0 mb-6">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">N° Commande</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Client</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Montant</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Statut</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
            </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
            @foreach($recentOrders as $o)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $o['id'] }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $o['customer'] }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $o['date'] }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $o['amount'] }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <x-admin.badge :text="$o['status']" :variant="$o['badge']" />
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        <button class="text-primary hover:text-secondary mr-2" type="button" title="Voir">
                            <i class="fas fa-eye"></i>
                        </button>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</x-admin.card>

{{-- Product management --}}
<x-admin.card title="Gestion des produits" class="p-0 mb-6">
    <x-slot:actions>
        <button
            class="px-4 py-2 bg-primary text-white text-sm font-medium rounded-md hover:bg-secondary"
            type="button"
            data-modal-open="addProductModal"
        >
            <i class="fas fa-plus mr-2"></i> Ajouter un produit
        </button>
    </x-slot:actions>

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Image</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nom</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Catégorie</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Prix</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stock</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
            </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
            @foreach($products as $p)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <img src="{{ $p['image'] }}" alt="Product" class="w-10 h-10 rounded">
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $p['name'] }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $p['category'] }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $p['price'] }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $p['stock'] }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        <button class="text-blue-500 hover:text-blue-700 mr-2" type="button" title="Modifier">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="text-red-500 hover:text-red-700" type="button" title="Supprimer">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</x-admin.card>


@endsection

@push('modals')
    <x-admin.modal id="addProductModal" title="Ajouter un nouveau produit">
        <form method="POST" action="#">
            @csrf

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Nom du produit</label>
                <input type="text" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary">
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Catégorie</label>
                <select class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary">
                    <option>Antidouleur</option>
                    <option>Antibiotique</option>
                    <option>Vitamines</option>
                    <option>Soins de la peau</option>
                </select>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Prix (FCFA)</label>
                <input type="number" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary">
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Quantité en stock</label>
                <input type="number" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary">
            </div>
        </form>

        <x-slot:footer>
            <button class="px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 hover:bg-gray-50"
                    type="button"
                    data-modal-close="addProductModal">
                Annuler
            </button>

            <button class="px-4 py-2 bg-primary text-white text-sm font-medium rounded-md hover:bg-secondary"
                    type="button"
                    data-modal-close="addProductModal">
                Enregistrer
            </button>
        </x-slot:footer>
    </x-admin.modal>
@endpush
