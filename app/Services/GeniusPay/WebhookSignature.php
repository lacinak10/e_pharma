<?php

namespace App\Services\GeniusPay;

/**
 * Vérification de la signature HMAC d'un webhook GeniusPay.
 *
 * Format : HMAC-SHA256(timestamp + "." + corps brut, secret).
 *
 * « Corps brut » est à prendre au pied de la lettre : les octets reçus, pas un
 * ré-encodage. La page publique geniuspay.ci/docs/api montre un exemple qui
 * signe json_encode($request->all()) — c'est faux, et silencieusement : PHP y
 * réécrit « "amount": 10000.00 » en « 10000.0 », et la signature ne tombe plus.
 * Le guide du tableau de bord marchand, lui, signe bien
 * file_get_contents('php://input').
 *
 * Vérifié en sandbox le 13 septembre 2026 sur deux livraisons réelles.
 */
class WebhookSignature
{
    /** Fenêtre anti-rejeu, en secondes (valeur recommandée par GeniusPay). */
    public const TOLERANCE_SECONDS = 300;

    public function verify(string $rawBody, string $timestamp, string $signature, string $secret): bool
    {
        if ($signature === '' || $timestamp === '' || $secret === '') {
            return false;
        }

        // hash_equals : comparaison à temps constant, pas de fuite par timing.
        return hash_equals(
            hash_hmac('sha256', $timestamp . '.' . $rawBody, $secret),
            $signature,
        );
    }

    /** L'horodatage est-il dans la fenêtre acceptée ? Protège du rejeu. */
    public function timestampIsFresh(string $timestamp, ?int $now = null): bool
    {
        if (! is_numeric($timestamp)) {
            return false;
        }

        return abs(($now ?? time()) - (int) $timestamp) <= self::TOLERANCE_SECONDS;
    }
}
