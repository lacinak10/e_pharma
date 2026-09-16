<?php

namespace App\Enums;

/**
 * Cycle de vie d'une commande ePharma — les 14 statuts du système de design.
 *
 * Un statut = un libellé, une couleur, un acteur. Identiques dans les trois
 * interfaces (client, manager, livreur). Cette enum est la source de vérité :
 * aucune vue ne doit re-déclarer sa propre table de correspondance.
 */
enum OrderStatus: string
{
    case PENDING_VALIDATION  = 'PENDING_VALIDATION';   // 1 — Manager
    case REFUSED             = 'REFUSED';              // 2 — Manager
    case CHECKING            = 'CHECKING';             // 3 — Manager
    case AVAILABLE           = 'AVAILABLE';            // 4 — Manager
    case PARTIALLY_AVAILABLE = 'PARTIALLY_AVAILABLE';  // 5 — Manager → client
    case UNAVAILABLE         = 'UNAVAILABLE';          // 6 — Manager
    case COURIER_ASSIGNED    = 'COURIER_ASSIGNED';     // 7 — Manager
    case TO_PHARMACY         = 'TO_PHARMACY';          // 8 — Livreur
    case AT_PHARMACY         = 'AT_PHARMACY';          // 9 — Livreur
    case PICKED_UP           = 'PICKED_UP';            // 10 — Livreur
    case TO_CLIENT           = 'TO_CLIENT';            // 11 — Livreur
    case DELIVERED           = 'DELIVERED';            // 12 — Livreur
    case RATED               = 'RATED';                // 13 — Client
    case CANCELED            = 'CANCELED';             // 14 — Client / manager

    /** Numéro d'ordre dans le cycle de vie (colonne « N° » du système de design). */
    public function number(): int
    {
        return match ($this) {
            self::PENDING_VALIDATION  => 1,
            self::REFUSED             => 2,
            self::CHECKING            => 3,
            self::AVAILABLE           => 4,
            self::PARTIALLY_AVAILABLE => 5,
            self::UNAVAILABLE         => 6,
            self::COURIER_ASSIGNED    => 7,
            self::TO_PHARMACY         => 8,
            self::AT_PHARMACY         => 9,
            self::PICKED_UP           => 10,
            self::TO_CLIENT           => 11,
            self::DELIVERED           => 12,
            self::RATED               => 13,
            self::CANCELED            => 14,
        };
    }

    /** Libellé court affiché dans le badge. */
    public function badge(): string
    {
        return match ($this) {
            self::PENDING_VALIDATION  => 'En attente de validation',
            self::REFUSED             => 'Refusée',
            self::CHECKING            => 'Vérification',
            self::AVAILABLE           => 'Disponible',
            self::PARTIALLY_AVAILABLE => 'Partiellement dispo.',
            self::UNAVAILABLE         => 'Indisponible',
            self::COURIER_ASSIGNED    => 'Livreur assigné',
            self::TO_PHARMACY         => 'En route pharmacie',
            self::AT_PHARMACY         => 'Arrivé pharmacie',
            self::PICKED_UP           => 'Médicament récupéré',
            self::TO_CLIENT           => 'En route vers vous',
            self::DELIVERED           => 'Livrée',
            self::RATED               => 'Notée',
            self::CANCELED            => 'Annulée',
        };
    }

    /** Phrase complète adressée au client. */
    public function label(): string
    {
        return match ($this) {
            self::PENDING_VALIDATION  => 'Votre commande a été prise en charge et est en attente de validation par le manager.',
            self::REFUSED             => 'Commande refusée par le manager, avec le motif.',
            self::CHECKING            => 'Vos médicaments sont en cours de vérification auprès de nos pharmacies partenaires. Résultat dans moins de 5 minutes.',
            self::AVAILABLE           => 'Médicaments disponibles, commande validée.',
            self::PARTIALLY_AVAILABLE => 'Une partie seulement est disponible — vous choisissez comment continuer.',
            self::UNAVAILABLE         => 'Médicament introuvable chez nos partenaires, avec des alternatives proposées.',
            self::COURIER_ASSIGNED    => 'Livreur assigné : nom, numéro et délai estimé.',
            self::TO_PHARMACY         => 'Le livreur est en route vers la pharmacie.',
            self::AT_PHARMACY         => 'Le livreur est arrivé à la pharmacie.',
            self::PICKED_UP           => 'Le médicament a été récupéré.',
            self::TO_CLIENT           => 'Le livreur est en route vers vous.',
            self::DELIVERED           => 'Commande livrée.',
            self::RATED               => 'Livreur noté sur 5 avec un commentaire.',
            self::CANCELED            => 'Annulée, avec le motif et son auteur.',
        };
    }

    /** Qui fait avancer la commande à ce stade. */
    public function actor(): string
    {
        return match ($this) {
            self::PENDING_VALIDATION, self::REFUSED, self::CHECKING,
            self::AVAILABLE, self::UNAVAILABLE, self::COURIER_ASSIGNED => 'Manager',
            self::PARTIALLY_AVAILABLE                                  => 'Manager → client',
            self::TO_PHARMACY, self::AT_PHARMACY, self::PICKED_UP,
            self::TO_CLIENT, self::DELIVERED                           => 'Livreur',
            self::RATED                                                => 'Client',
            self::CANCELED                                             => 'Client / manager',
        };
    }

    /**
     * Famille de couleur du design system.
     * green = confirmé · amber = attente · red = indisponible · blue = livreur en mouvement · neutral = clos
     */
    public function tone(): string
    {
        return match ($this) {
            self::AVAILABLE, self::DELIVERED, self::RATED                    => 'green',
            self::PENDING_VALIDATION, self::CHECKING, self::PARTIALLY_AVAILABLE => 'amber',
            self::REFUSED, self::UNAVAILABLE                                 => 'red',
            self::COURIER_ASSIGNED, self::TO_PHARMACY, self::AT_PHARMACY,
            self::PICKED_UP, self::TO_CLIENT                                 => 'blue',
            self::CANCELED                                                   => 'neutral',
        };
    }

    /** Couleur de texte / pastille du badge. */
    public function color(): string
    {
        return match ($this->tone()) {
            'green'   => '#0E5C43',
            'amber'   => '#B87514',
            'red'     => '#A6382F',
            'blue'    => '#33557F',
            default   => '#5B6B63',
        };
    }

    /** Fond du badge. */
    public function tint(): string
    {
        return match ($this->tone()) {
            'green'   => '#E7F1EC',
            'amber'   => '#FBF0DC',
            'red'     => '#F9EAE7',
            'blue'    => '#E9EFF6',
            default   => '#EFEDE6',
        };
    }

    /** La commande attend une action du manager. */
    public function needsManager(): bool
    {
        return in_array($this, [
            self::PENDING_VALIDATION,
            self::CHECKING,
            self::AVAILABLE,
            self::PARTIALLY_AVAILABLE,
        ], true);
    }

    /** Le verdict est rendu et permet d'engager une livraison. */
    public function isVerdictFavorable(): bool
    {
        return in_array($this, [self::AVAILABLE, self::PARTIALLY_AVAILABLE], true);
    }

    /** Une course est en cours chez un livreur. */
    public function isCourierPhase(): bool
    {
        return in_array($this, [
            self::COURIER_ASSIGNED,
            self::TO_PHARMACY,
            self::AT_PHARMACY,
            self::PICKED_UP,
            self::TO_CLIENT,
        ], true);
    }

    /** Plus aucune transition possible. */
    public function isFinal(): bool
    {
        return in_array($this, [self::REFUSED, self::UNAVAILABLE, self::RATED, self::CANCELED], true);
    }

    /** Le client peut encore annuler lui-même. */
    public function isCancelableByClient(): bool
    {
        return in_array($this, [self::PENDING_VALIDATION, self::CHECKING, self::AVAILABLE], true);
    }

    /** Étape courante (1..5) du suivi de livraison, ou null hors phase de course. */
    public function deliveryStep(): ?int
    {
        return match ($this) {
            self::TO_PHARMACY => 1,
            self::AT_PHARMACY => 2,
            self::PICKED_UP   => 3,
            self::TO_CLIENT   => 4,
            self::DELIVERED, self::RATED => 5,
            default => null,
        };
    }

    /** Statut suivant quand le livreur fait avancer sa course. */
    public function nextCourierStep(): ?self
    {
        return match ($this) {
            self::COURIER_ASSIGNED => self::TO_PHARMACY,
            self::TO_PHARMACY      => self::AT_PHARMACY,
            self::AT_PHARMACY      => self::PICKED_UP,
            self::PICKED_UP        => self::TO_CLIENT,
            self::TO_CLIENT        => self::DELIVERED,
            default                => null,
        };
    }

    /** Libellé du bouton d'avancement côté livreur. */
    public function courierActionLabel(): ?string
    {
        return match ($this) {
            self::COURIER_ASSIGNED => 'Je pars vers la pharmacie',
            self::TO_PHARMACY      => 'Je suis arrivé à la pharmacie',
            self::AT_PHARMACY      => "J'ai récupéré les médicaments",
            self::PICKED_UP        => 'Je pars vers le client',
            self::TO_CLIENT        => 'Commande livrée',
            default                => null,
        };
    }

    /** Classe utilitaire du design system (ep-badge--green, …). */
    public function badgeClass(): string
    {
        return 'ep-badge ep-badge--' . $this->tone();
    }

    /** @return array<int, self> Les 14 statuts dans l'ordre du cycle de vie. */
    public static function lifecycle(): array
    {
        $all = self::cases();
        usort($all, fn (self $a, self $b) => $a->number() <=> $b->number());

        return $all;
    }
}
