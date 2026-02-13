@props([
    'action' => '#',
    'q' => null,
    'category' => null,
    'status' => null,
    'categories' => [],
])

<x-admin.card class="mb-6">
    <form method="GET" action="{{ $action }}">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div class="relative flex-1">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class="fas fa-search text-gray-400"></i>
                </div>
                <x-admin.input
                    name="q"
                    :value="$q"
                    placeholder="Rechercher un produit..."
                    class="pl-10"
                />
            </div>

            <div class="flex flex-col md:flex-row gap-2 w-full md:w-auto">
                <x-admin.select name="category" class="md:w-56">
                    <option value="">Toutes catégories</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->name }}" {{ (string)$category === (string)$cat->name ? 'selected' : '' }}>
                            {{ $cat->name }}
                        </option>
                    @endforeach
                </x-admin.select>

                <x-admin.select name="status" class="md:w-48">
                    <option value="">Tous les statuts</option>
                    <option value="in_stock" {{ $status === 'in_stock' ? 'selected' : '' }}>En stock</option>
                    <option value="low_stock" {{ $status === 'low_stock' ? 'selected' : '' }}>Stock faible</option>
                    <option value="out_of_stock" {{ $status === 'out_of_stock' ? 'selected' : '' }}>Épuisé</option>
                </x-admin.select>

                <x-admin.button type="submit" variant="primary" icon="fas fa-filter">
                    Filtrer
                </x-admin.button>
            </div>
        </div>
    </form>
</x-admin.card>
