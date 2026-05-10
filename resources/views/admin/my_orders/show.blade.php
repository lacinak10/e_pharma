@extends('layouts.admin')

@section('title','E-PHARMA - Détail livraison')
@section('page_title','Détail livraison')

@section('content')
@if(session('success'))
    <div class="mb-4 p-3 rounded-lg bg-green-50 border border-green-200 text-green-800 text-sm">
        {{ session('success') }}
    </div>
@endif

<x-admin.card title="Commande #EP-{{ $order->id }}">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="p-4 rounded-lg bg-gray-50 border border-gray-100">
            <div class="text-xs text-gray-500">Client</div>
            <div class="text-sm font-semibold text-gray-900">{{ $order->user?->name ?? 'Client' }}</div>
        </div>
        <div class="p-4 rounded-lg bg-gray-50 border border-gray-100">
            <div class="text-xs text-gray-500">Téléphone</div>
            <div class="text-sm font-semibold text-gray-900">{{ $order->delivery_phone ?? '—' }}</div>
        </div>

        <div class="p-4 rounded-lg bg-gray-50 border border-gray-100 md:col-span-2">
            <div class="text-xs text-gray-500">Adresse</div>
            <div class="text-sm font-semibold text-gray-900">{{ $order->delivery_address }}</div>
        </div>
    </div>

    <div class="mt-6">
        <div class="text-sm font-semibold text-gray-800 mb-2">Articles</div>
        <div class="overflow-x-auto rounded-lg border border-gray-200">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                <tr>
                    <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase">Produit</th>
                    <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase">Qté</th>
                </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                @foreach($order->items as $it)
                    <tr>
                        <td class="px-5 py-3 text-sm text-gray-800">{{ $it->medicine?->name ?? 'Produit' }}</td>
                        <td class="px-5 py-3 text-sm text-gray-700">{{ $it->quantity }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>

    @if($order->has_prescription)
    <div class="mt-6 p-5 rounded-xl bg-purple-50 border border-purple-200">
        <div class="flex items-center gap-2 mb-3">
            <i class="fa-solid fa-file-medical text-purple-600 text-lg"></i>
            <span class="text-sm font-semibold text-purple-900">Ordonnance jointe</span>
            @php $ext = strtoupper(pathinfo($order->prescription_path, PATHINFO_EXTENSION)); @endphp
            <span class="ml-auto text-xs text-purple-600 font-mono">{{ $ext }}</span>
        </div>
        <a href="{{ route('courier.my_orders.prescription', $order) }}"
           class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-purple-600 text-white text-sm font-semibold hover:bg-purple-700 transition">
            <i class="fa-solid fa-download"></i> Télécharger l'ordonnance
        </a>
    </div>
    @endif

    {{-- Actions livreur selon statut assignment --}}
    @php $a = $order->assignment; @endphp
    <div class="pt-4 border-t mt-6 flex flex-col md:flex-row gap-2 md:items-center md:justify-between">
        <a href="{{ route('courier.my_orders.index') }}" class="text-sm text-gray-700 hover:text-gray-900">
            ← Retour
        </a>

        <div class="flex flex-wrap gap-2">
            @if($a?->status === App\Enums\AssignmentStatus::ASSIGNED)
                <form method="POST" action="{{ route('courier.my_orders.accept',$order) }}">
                    @csrf @method('PATCH')
                    <x-admin.button type="submit" variant="primary" icon="fa-solid fa-circle-check">Accepter</x-admin.button>
                </form>
                <form method="POST" action="{{ route('courier.my_orders.refuse',$order) }}">
                    @csrf @method('PATCH')
                    <x-admin.button type="submit" variant="outline" icon="fa-solid fa-circle-xmark">Refuser</x-admin.button>
                </form>
            @elseif($a?->status === App\Enums\AssignmentStatus::ACCEPTED)
                <form method="POST" action="{{ route('courier.my_orders.start',$order) }}">
                    @csrf @method('PATCH')
                    <x-admin.button type="submit" variant="primary" icon="fa-solid fa-truck">Démarrer livraison</x-admin.button>
                </form>
            @elseif($a?->status === App\Enums\AssignmentStatus::DELIVERING)
                <form method="POST" action="{{ route('courier.my_orders.delivered',$order) }}">
                    @csrf @method('PATCH')
                    <x-admin.button type="submit" variant="primary" icon="fa-solid fa-circle-check">Marquer livrée</x-admin.button>
                </form>
            @else
                <span class="text-sm text-gray-500">Statut: {{ $a?->status?->label() ?? '—' }}</span>
            @endif
        </div>
    </div>
</x-admin.card>
@endsection
