<?php

namespace App\Enums;

enum OrderStatus: string
{
    case PENDING_ASSIGNMENT = 'PENDING_ASSIGNMENT'; // En attente d’un livreur
    case ASSIGNED           = 'ASSIGNED';           // Affectée
    case ACCEPTED           = 'ACCEPTED';           // Acceptée par livreur
    case IN_DELIVERY        = 'IN_DELIVERY';        // En cours de livraison
    case DELIVERED          = 'DELIVERED';          // Livrée
    case REFUSED            = 'REFUSED';            // Refusée
    case CANCELED           = 'CANCELED';           // Annulée

    public function label(): string
    {
        return match ($this) {
            self::PENDING_ASSIGNMENT => 'En attente d’un livreur',
            self::ASSIGNED => 'Affectée',
            self::ACCEPTED => 'Acceptée',
            self::IN_DELIVERY => 'En cours de livraison',
            self::DELIVERED => 'Livrée',
            self::REFUSED => 'Refusée',
            self::CANCELED => 'Annulée',
        };
    }
}
