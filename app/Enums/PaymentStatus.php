<?php

namespace App\Enums;

/**
 * Cycle de vie d'un paiement en ligne GeniusPay.
 *
 * Axe distinct de OrderStatus : une commande garde ses 14 statuts logistiques,
 * le paiement avance de son côté. Les valeurs reprennent telles quelles celles
 * de l'API GeniusPay (« cancelled » à l'anglaise, donc) pour qu'un payload
 * entrant se convertisse sans table de passage.
 *
 * Comme OrderStatus, cette enum est la source de vérité de sa présentation :
 * aucune vue ne redéclare sa propre correspondance statut → libellé/couleur.
 */
enum PaymentStatus: string
{
    case PENDING    = 'pending';      // lien créé, le client n'a pas encore payé
    case PROCESSING = 'processing';   // en cours chez l'opérateur
    case COMPLETED  = 'completed';    // encaissé
    case FAILED     = 'failed';       // échec (solde, réseau, refus)
    case CANCELLED  = 'cancelled';    // abandonné par le client
    case REFUNDED   = 'refunded';     // remboursé depuis le dashboard GeniusPay
    case EXPIRED    = 'expired';      // lien non utilisé dans les 24 h

    /** Libellé court affiché dans le badge. */
    public function badge(): string
    {
        return match ($this) {
            self::PENDING    => 'Paiement en attente',
            self::PROCESSING => 'Paiement en cours',
            self::COMPLETED  => 'Payée',
            self::FAILED     => 'Paiement échoué',
            self::CANCELLED  => 'Paiement annulé',
            self::REFUNDED   => 'Remboursée',
            self::EXPIRED    => 'Lien expiré',
        };
    }

    /** Phrase complète adressée au client. */
    public function label(): string
    {
        return match ($this) {
            self::PENDING    => "Votre paiement n'est pas encore finalisé. Le livreur part dès qu'il est confirmé.",
            self::PROCESSING => 'Votre opérateur traite le paiement. La confirmation arrive dans un instant.',
            self::COMPLETED  => 'Paiement confirmé. Votre commande part en livraison.',
            self::FAILED     => "Le paiement n'a pas abouti. Vous pouvez réessayer.",
            self::CANCELLED  => 'Vous avez interrompu le paiement. Vous pouvez le reprendre.',
            self::REFUNDED   => 'Le montant vous a été remboursé.',
            self::EXPIRED    => 'Le lien de paiement a expiré. Générez-en un nouveau.',
        };
    }

    /**
     * Famille de couleur du design system — mêmes conventions que OrderStatus :
     * vert = confirmé · ambre = attente · rouge = échec · neutre = clos.
     */
    public function tone(): string
    {
        return match ($this) {
            self::COMPLETED             => 'green',
            self::PENDING, self::PROCESSING => 'amber',
            self::FAILED, self::EXPIRED => 'red',
            default                     => 'neutral',
        };
    }

    /** Le paiement est encaissé : la commande peut partir en livraison. */
    public function isSuccessful(): bool
    {
        return $this === self::COMPLETED;
    }

    /** Plus aucune transition attendue de la part du client. */
    public function isFinal(): bool
    {
        return in_array($this, [self::COMPLETED, self::REFUNDED], true);
    }

    /** Le client peut relancer un paiement (échec, abandon, expiration). */
    public function isRetryable(): bool
    {
        return in_array($this, [self::FAILED, self::CANCELLED, self::EXPIRED], true);
    }

    /** Le paiement est parti mais son sort n'est pas encore connu. */
    public function isAwaiting(): bool
    {
        return in_array($this, [self::PENDING, self::PROCESSING], true);
    }
}
