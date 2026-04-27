@props(['status'])

@php
    $label = match($status) {
        'PENDING_ASSIGNMENT' => 'En attente de livreur',
        'ASSIGNED' => 'Affectée',
        'ACCEPTED' => 'Acceptée',
        'IN_DELIVERY' => 'En livraison',
        'DELIVERED' => 'Livrée',
        'REFUSED' => 'Refusée',
        'CANCELED' => 'Annulée',
        default => $status,
    };

    $variant = match($status) {
        'DELIVERED' => 'green',
        'IN_DELIVERY', 'ACCEPTED' => 'blue',
        'ASSIGNED' => 'yellow',
        'REFUSED', 'CANCELED' => 'red',
        default => 'gray',
    };
@endphp

<x-store.badge :variant="$variant">
    {{ $label }}
</x-store.badge>
