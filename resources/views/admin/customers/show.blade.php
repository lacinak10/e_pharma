@extends('layouts.admin')

@section('title','E-PHARMA - Détail client')
@section('page_title','Détail client')

@section('content')
<x-admin.card title="Client">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="p-4 rounded-lg bg-gray-50 border border-gray-100">
            <div class="text-xs text-gray-500">Nom</div>
            <div class="text-sm font-semibold text-gray-900">{{ $user->name }}</div>
        </div>
        <div class="p-4 rounded-lg bg-gray-50 border border-gray-100">
            <div class="text-xs text-gray-500">Email</div>
            <div class="text-sm font-semibold text-gray-900">{{ $user->email }}</div>
        </div>
    </div>

    <div class="pt-4 border-t mt-4 flex justify-end">
        <a href="{{ route('manager.customers.index') }}"
           class="px-4 py-2 rounded-lg border bg-white text-gray-700 hover:bg-gray-50">
            Retour
        </a>
    </div>
</x-admin.card>

<x-admin.card title="Commandes du client" class="p-0 mt-4">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Commande</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Montant</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Statut</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
            </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
            @forelse($orders as $o)
                <tr>
                    <td class="px-6 py-4 text-sm font-medium text-gray-900">#EP-{{ $o->id }}</td>
                    <td class="px-6 py-4 text-sm text-gray-500">{{ optional($o->created_at)->format('d/m/Y H:i') }}</td>
                    <td class="px-6 py-4 text-sm text-gray-700">{{ number_format((int)$o->total_amount,0,',',' ') }} FCFA</td>
                    <td class="px-6 py-4 text-sm text-gray-700">{{ $o->status }}</td>
                    <td class="px-6 py-4 text-sm">
                        <a href="{{ route('manager.orders.show',$o) }}" class="text-primary hover:text-secondary">
                            <i class="fa-regular fa-eye"></i>
                        </a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="px-6 py-10">
                    <x-admin.empty-state title="Aucune commande" description="Ce client n’a pas encore commandé." icon="fa-solid fa-clipboard-list" />
                </td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <x-admin.pagination :paginator="$orders" />
</x-admin.card>
@endsection
