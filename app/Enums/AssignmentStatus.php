<?php

namespace App\Enums;

enum AssignmentStatus: string
{
    case ASSIGNED = 'ASSIGNED';
    case ACCEPTED = 'ACCEPTED';
    case REFUSED  = 'REFUSED';

    public function label(): string
    {
        return match ($this) {
            self::ASSIGNED => 'Affectée',
            self::ACCEPTED => 'Acceptée',
            self::REFUSED  => 'Refusée',
        };
    }
}
