<?php

namespace App\Services\GeniusPay;

use RuntimeException;

/**
 * Échec d'un appel à l'API Marchand GeniusPay.
 *
 * Porte le code machine du fournisseur (MISSING_API_KEY, PAYMENT_INIT_FAILED,
 * TRANSACTION_NOT_FOUND…) pour que l'appelant décide sans relire le corps HTTP.
 */
class GeniusPayException extends RuntimeException
{
    public function __construct(
        string $message,
        public readonly string $errorCode = 'UNKNOWN',
        public readonly int $httpStatus = 0,
    ) {
        parent::__construct($message);
    }

    /** @param  array<string, mixed>  $body */
    public static function fromResponse(array $body, int $httpStatus): self
    {
        return new self(
            $body['error']['message'] ?? 'Appel GeniusPay en échec.',
            $body['error']['code'] ?? 'UNKNOWN',
            $httpStatus,
        );
    }

    /** La transaction n'existe pas chez GeniusPay. */
    public function isNotFound(): bool
    {
        return $this->errorCode === 'TRANSACTION_NOT_FOUND' || $this->httpStatus === 404;
    }

    /** Défaut de configuration : clés absentes, invalides, compte désactivé. */
    public function isConfigurationIssue(): bool
    {
        return in_array($this->errorCode, ['MISSING_API_KEY', 'INVALID_API_KEY', 'MERCHANT_INACTIVE'], true);
    }
}
