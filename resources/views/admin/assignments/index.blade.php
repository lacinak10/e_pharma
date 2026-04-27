@extends('layouts.admin')

@section('title','E-PHARMA - Affectations')
@section('page_title','Affectations')

@section('content')
@php
    use App\Enums\AssignmentStatus;
    $status = $status ?? request('status');
    $statusOptions = [
        ''                              => 'Tous',
        AssignmentStatus::ASSIGNED->value   => 'Assignée',
        AssignmentStatus::ACCEPTED->value   => 'Acceptée',
        AssignmentStatus::DELIVERING->value => 'En livraison',
        AssignmentStatus::DELIVERED->value  => 'Livrée',
        AssignmentStatus::REFUSED->value    => 'Refusée',
    ];
@endphp

@if(session('success'))
    <div class="mb-4 p-3 rounded-lg bg-green-50 border border-green-200 text-green-800 text-sm">
        {{ session('success') }}
    </div>
@endif

<x-admin.card title="Affectations" class="p-0">
    <x-slot:actions>
        <form method="GET" class="flex gap-2 items-center">
            <x-admin.select name="status">
                @foreach($statusOptions as $val => $txt)
                    <option value="{{ $val }}" {{ (string)$status === (string)$val ? 'selected' : '' }}>
                        {{ $txt }}
                    </option>
                @endforeach
            </x-admin.select>
            <x-admin.button type="submit" variant="outline" icon="fa-solid fa-magnifying-glass">Filtrer</x-admin.button>

            @if($status)
                <a href="{{ route('manager.assignments.index') }}"
                   class="px-3 py-2 rounded-lg border bg-white text-gray-700 hover:bg-gray-50 text-sm">
                    Réinitialiser
                </a>
            @endif
        </form>
    </x-slot:actions>

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Commande</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Client</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Livreur</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Assignée le</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Statut</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Note</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
            </tr>
            </thead>

            <tbody class="bg-white divide-y divide-gray-200">
            @forelse($assignments as $a)
                <tr>
                    <td class="px-6 py-4 text-sm font-medium text-gray-900">#EP-{{ $a->order_id }}</td>
                    <td class="px-6 py-4 text-sm text-gray-700">{{ $a->order?->user?->name ?? 'Client' }}</td>
                    <td class="px-6 py-4 text-sm text-gray-700">{{ $a->courier?->name ?? '—' }}</td>
                    <td class="px-6 py-4 text-sm text-gray-500">{{ optional($a->assigned_at)->format('d/m/Y H:i') }}</td>
                    <td class="px-6 py-4">
                        <x-admin.badge :text="$a->status->label()" :variant="$a->status->badge()" />
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500">{{ $a->note ?? '—' }}</td>
                    <td class="px-6 py-4 text-sm">
                        @if($a->order_id)
                            <a href="{{ route('manager.orders.show',$a->order_id) }}" class="text-primary hover:text-secondary" title="Voir commande">
                                <i class="fa-regular fa-eye"></i>
                            </a>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="px-6 py-10">
                    <x-admin.empty-state title="Aucune affectation" description="Aucune affectation trouvée." icon="fa-solid fa-user-check" />
                </td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <x-admin.pagination :paginator="$assignments" />
</x-admin.card>
@endsection
