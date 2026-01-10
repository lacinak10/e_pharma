@props(['status'])

@php
    $steps = [
        ['key' => 'PENDING_ASSIGNMENT', 'label' => 'Commande confirmée'],
        ['key' => 'ASSIGNED', 'label' => 'Livreur affecté'],
        ['key' => 'IN_DELIVERY', 'label' => 'En cours de livraison'],
        ['key' => 'DELIVERED', 'label' => 'Livrée'],
    ];

    $rank = [
        'PENDING_ASSIGNMENT' => 1,
        'ASSIGNED' => 2,
        'ACCEPTED' => 2,
        'IN_DELIVERY' => 3,
        'DELIVERED' => 4,
        'REFUSED' => 0,
        'CANCELED' => 0,
    ];

    $current = $rank[$status] ?? 1;
    $failed = in_array($status, ['REFUSED','CANCELED'], true);
@endphp

<div class="bg-white rounded-2xl border border-gray-100 p-6">
    <div class="flex items-center justify-between">
        <h3 class="text-lg font-extrabold text-gray-900">Suivi</h3>
        <x-store.order-status :status="$status" />
    </div>

    @if($failed)
        <div class="mt-4 rounded-2xl bg-red-50 border border-red-100 p-4 text-sm text-red-700">
            Cette commande a été <b>{{ $status === 'CANCELED' ? 'annulée' : 'refusée' }}</b>.
        </div>
    @endif

    <ol class="mt-6 space-y-4">
        @foreach($steps as $idx => $step)
            @php
                $stepRank = $idx + 1;
                $done = !$failed && $current >= $stepRank;
                $active = !$failed && $current === $stepRank;
            @endphp

            <li class="flex items-start gap-4">
                <div class="mt-1 w-9 h-9 rounded-xl flex items-center justify-center
                    {{ $done ? 'bg-green-100 text-green-700' : ($active ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-400') }}">
                    <i class="fa-solid {{ $done ? 'fa-check' : ($active ? 'fa-circle-dot' : 'fa-circle') }}"></i>
                </div>
                <div class="flex-1">
                    <div class="font-bold text-gray-900">{{ $step['label'] }}</div>
                    <div class="text-sm text-gray-500">
                        {{ $done ? 'Terminé' : ($active ? 'En cours' : 'À venir') }}
                    </div>
                </div>
            </li>
        @endforeach
    </ol>
</div>
