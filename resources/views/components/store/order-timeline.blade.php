@props(['status'])

@php
    // Convertit l'enum en clé string (value si backed enum, sinon name)
    $statusKey = $status instanceof \BackedEnum
        ? $status->value
        : ($status instanceof \UnitEnum ? $status->name : (string) $status);

    $statusKey = strtoupper((string) $statusKey);

    $failedStatuses = ['REFUSED', 'CANCELED'];
    $failed = in_array($statusKey, $failedStatuses, true);

    $baseSteps = [
        ['key' => 'PENDING_ASSIGNMENT', 'label' => 'Commande confirmée'],
        ['key' => 'ASSIGNED',           'label' => 'Livreur affecté'],
        ['key' => 'IN_DELIVERY',        'label' => 'En cours de livraison'],
        ['key' => 'DELIVERED',          'label' => 'Livrée'],
    ];

    $rank = [
        'PENDING_ASSIGNMENT' => 1,
        'ASSIGNED'           => 2,
        'ACCEPTED'           => 2,
        'IN_DELIVERY'        => 3,
        'DELIVERED'          => 4,
        'REFUSED'            => 0,
        'CANCELED'           => 0,
    ];

    $failedProgressRank = [
        'CANCELED' => 1,
        'REFUSED'  => 2,
    ];

    $currentRank = $rank[$statusKey] ?? 1;

    $stageRank = $failed
        ? ($failedProgressRank[$statusKey] ?? 1)
        : $currentRank;

    $steps = $baseSteps;

    if ($failed) {
        $steps[] = [
            'key'       => $statusKey,
            'label'     => $statusKey === 'CANCELED' ? 'Annulée' : 'Refusée',
            'isFailure' => true,
        ];
    }
@endphp

<div class="bg-white rounded-2xl border border-gray-100 p-6">
    <div class="flex items-center justify-between">
        <h3 class="text-lg font-extrabold text-gray-900">Suivi</h3>

        {{-- Si ton composant attend l'enum, garde $status --}}
        <x-store.order-status :status="$status" />

        {{-- Si ton composant attend une string, utilise plutôt : --}}
        {{-- <x-store.order-status :status="$statusKey" /> --}}
    </div>

    @if($failed)
        <div class="mt-4 rounded-2xl bg-red-50 border border-red-100 p-4 text-sm text-red-700">
            Cette commande a été <b>{{ $statusKey === 'CANCELED' ? 'annulée' : 'refusée' }}</b>.
        </div>
    @endif

    <ol class="mt-6 space-y-4">
        @foreach($steps as $idx => $step)
            @php
                $stepRank = $idx + 1;
                $isFailureStep = (bool) ($step['isFailure'] ?? false);

                if ($isFailureStep) {
                    $done = true;
                    $active = true;
                    $stateLabel = 'Échec';
                } else {
                    $done = $failed ? ($stepRank <= $stageRank) : ($stepRank < $currentRank);
                    $active = (!$failed && $stepRank === $currentRank);
                    $stateLabel = $done ? 'Terminé' : ($active ? 'En cours' : 'À venir');
                }

                $classes = match (true) {
                    $isFailureStep => 'bg-red-100 text-red-700',
                    $done => 'bg-green-100 text-green-700',
                    $active => 'bg-blue-100 text-blue-700',
                    default => 'bg-gray-100 text-gray-400',
                };

                $icon = match (true) {
                    $isFailureStep => 'fa-xmark',
                    $done => 'fa-check',
                    $active => 'fa-circle-dot',
                    default => 'fa-circle',
                };
            @endphp

            <li class="flex items-start gap-4">
                <div class="mt-1 w-9 h-9 rounded-xl flex items-center justify-center {{ $classes }}">
                    <i class="fa-solid {{ $icon }}"></i>
                </div>

                <div class="flex-1">
                    <div class="font-bold text-gray-900">{{ $step['label'] }}</div>
                    <div class="text-sm text-gray-500">{{ $stateLabel }}</div>
                </div>
            </li>
        @endforeach
    </ol>
</div>
