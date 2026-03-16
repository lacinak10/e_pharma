<?php

namespace App\Enums;

enum AssignmentStatus: string
{
    case ASSIGNED   = 'assigned';
    case ACCEPTED   = 'accepted';
    case REFUSED    = 'refused';
    case DELIVERING = 'delivering';
    case DELIVERED  = 'delivered';

    public function label(): string
    {
        return match ($this) {
            self::ASSIGNED   => 'Affectée',
            self::ACCEPTED   => 'Acceptée',
            self::REFUSED    => 'Refusée',
            self::DELIVERING => 'En livraison',
            self::DELIVERED  => 'Livrée',
        };
    }

    public function badge(): string
    {
        return match ($this) {
            self::ASSIGNED   => 'blue',
            self::ACCEPTED   => 'yellow',
            self::DELIVERING => 'indigo',
            self::DELIVERED  => 'green',
            self::REFUSED    => 'red',
        };
    }
}
