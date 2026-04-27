@extends('layouts.store')

@section('title', 'Mes commandes — E-PHARMA')

@section('content')
<section class="max-w-7xl mx-auto px-4 py-10">
    <div class="flex items-end justify-between gap-4">
        <div>
            <h1 class="text-3xl font-extrabold">Mes commandes</h1>
            <p class="text-gray-600 mt-1">Suivez l’évolution en temps réel.</p>
        </div>
        <a href="{{ route('store.medicines.index') }}" class="text-blue-700 font-semibold hover:underline">Commander →</a>
    </div>

    <div class="mt-8 bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                    <tr>
                        <th class="text-left px-6 py-4">Commande</th>
                        <th class="text-left px-6 py-4">Date</th>
                        <th class="text-left px-6 py-4">Total</th>
                        <th class="text-left px-6 py-4">Statut</th>
                        <th class="text-right px-6 py-4">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($orders as $order)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 font-bold">#{{ $order->id }}</td>
                            <td class="px-6 py-4 text-sm text-gray-600">{{ $order->created_at->format('d/m/Y H:i') }}</td>
                            <td class="px-6 py-4 font-extrabold text-gray-900">
                                {{ number_format((int)$order->total_amount, 0, ',', ' ') }} FCFA
                            </td>
                            <td class="px-6 py-4">
                                <x-store.order-status :status="$order->status->value" />
                            </td>
                            <td class="px-6 py-4 text-right">
                                <a href="{{ route('store.orders.show', $order) }}" class="text-blue-700 font-semibold hover:underline">
                                    Voir →
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="p-10">
                                <x-store.empty title="Aucune commande" subtitle="Vos commandes apparaîtront ici." />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4">
            {{ $orders->links() }}
        </div>
    </div>
</section>
@endsection
