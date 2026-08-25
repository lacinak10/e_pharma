<?php

namespace App\Enums;

/** Verdict de disponibilité rendu par la pharmacie partenaire pour une ligne de commande. */
enum ItemAvailability: string
{
    case PENDING     = 'pending';
    case AVAILABLE   = 'available';
    case UNAVAILABLE = 'unavailable';
    case SUBSTITUTED = 'substituted';

    public function label(): string
    {
        return match ($this) {
            self::PENDING     => 'À vérifier',
            self::AVAILABLE   => 'Disponible',
            self::UNAVAILABLE => 'Indisponible',
            self::SUBSTITUTED => 'Substitut proposé',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::AVAILABLE   => '#0E5C43',
            self::PENDING,
            self::SUBSTITUTED => '#B87514',
            self::UNAVAILABLE => '#A6382F',
        };
    }

    public function tint(): string
    {
        return match ($this) {
            self::AVAILABLE   => '#E7F1EC',
            self::PENDING,
            self::SUBSTITUTED => '#FBF0DC',
            self::UNAVAILABLE => '#F9EAE7',
        };
    }

    public function isSettled(): bool
    {
        return $this !== self::PENDING;
    }
}
