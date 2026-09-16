<?php

namespace App\Services\GeniusPay;

use App\Models\Order;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

/**
 * Client de l'API Marchand GeniusPay.
 *
 * Ne connaît que le protocole HTTP du fournisseur : il n'écrit rien en base et
 * ne touche jamais au statut d'une commande. Les transitions restent l'affaire
 * exclusive d'OrderWorkflow.
 */
class GeniusPayClient
{
    /** Montant minimum accepté par GeniusPay, en XOF. */
    public const MIN_AMOUNT = 200;

    /**
     * Crée une transaction et retourne de quoi rediriger le client.
     *
     * @return array{reference: string, checkout_url: ?string, status: string,
     *               environment: ?string, expires_at: ?string, amount: int, method: ?string}
     *
     * @throws GeniusPayException
     */
    public function createPayment(Order $order): array
    {
        $amount = (int) $order->total_amount;

        if ($amount < self::MIN_AMOUNT) {
            throw new GeniusPayException(
                "Montant inférieur au minimum GeniusPay ({$amount} FCFA).",
                'VALIDATION_ERROR',
            );
        }

        $payload = [
            'amount'      => $amount,
            'currency'    => 'XOF',
            'description' => "Commande {$order->reference} — ePharma",
            'customer'    => array_filter([
                'name'    => $order->client?->name,
                'email'   => $order->client?->email,
                'phone'   => self::toInternational($order->delivery_phone ?? $order->client?->phone),
                'country' => 'CI',
            ]),
            'success_url' => route('store.payments.return', $order),
            'error_url'   => route('store.payments.return', $order),
            'metadata'    => [
                'order_id'  => (string) $order->id,
                'reference' => $order->reference,
            ],
        ];

        /*
         * payment_method omis pour le Mobile Money : le client choisit Wave,
         * Orange, MTN ou Moov sur la page hébergée GeniusPay, ce que la
         * documentation recommande pour la conversion. Seule la carte est
         * envoyée explicitement, faute d'écran intermédiaire utile.
         */
        if ($order->payment_method === 'card') {
            $payload['payment_method'] = 'card';
        }

        $data = $this->send('post', '/payments', $payload);

        /*
         * GeniusPay réécho le montant. S'il diverge de ce qu'on a demandé, on
         * refuse tout de suite : enregistrer le nôtre ferait échouer plus tard
         * le contrôle de montant du webhook (paiement bloqué alors que le
         * client a payé), et enregistrer le leur reviendrait à accepter
         * silencieusement un montant qu'on n'a jamais validé.
         */
        $confirmed = (int) round((float) ($data['amount'] ?? 0));

        if ($confirmed !== $amount) {
            throw new GeniusPayException(
                "Montant confirmé ({$confirmed}) différent du montant demandé ({$amount}).",
                'AMOUNT_MISMATCH',
            );
        }

        return [
            'reference'    => (string) ($data['reference'] ?? ''),
            'checkout_url' => $data['checkout_url'] ?? $data['payment_url'] ?? null,
            'status'       => (string) ($data['status'] ?? 'pending'),
            'environment'  => $data['environment'] ?? null,
            'expires_at'   => $data['expires_at'] ?? null,
            'amount'       => $confirmed,
            'method'       => $data['payment_method'] ?? $data['gateway'] ?? null,
        ];
    }

    /**
     * Relit une transaction chez GeniusPay — source de vérité de la
     * réconciliation et du retour client.
     *
     * @return array<string, mixed>
     *
     * @throws GeniusPayException
     */
    public function fetchPayment(string $reference): array
    {
        return $this->send('get', '/payments/' . rawurlencode($reference));
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>  le contenu de `data`
     *
     * @throws GeniusPayException
     */
    private function send(string $method, string $uri, array $payload = []): array
    {
        try {
            $response = $this->request()->{$method}($uri, $payload);
        } catch (ConnectionException $e) {
            throw new GeniusPayException(
                'GeniusPay est injoignable : ' . $e->getMessage(),
                'CONNECTION_FAILED',
            );
        }

        $body = $response->json() ?? [];

        if ($response->failed() || ($body['success'] ?? false) !== true) {
            throw GeniusPayException::fromResponse($body, $response->status());
        }

        return $body['data'] ?? [];
    }

    private function request(): PendingRequest
    {
        $config = config('services.geniuspay');

        return Http::baseUrl($config['base_url'])
            ->withHeaders([
                'X-API-Key'    => $config['key'],
                'X-API-Secret' => $config['secret'],
            ])
            ->acceptJson()
            ->asJson()
            ->timeout(20)
            ->connectTimeout(8)
            /*
             * On ne rejoue que les échecs de connexion. Rejouer un POST parti
             * mais dont la réponse s'est perdue créerait un second lien de
             * paiement pour la même commande.
             */
            ->retry(2, 300, fn ($exception) => $exception instanceof ConnectionException, throw: false);
    }

    /**
     * « 0700000000 » → « +2250700000000 ».
     *
     * La validation du checkout accepte les deux formes (cf. CheckoutRequest),
     * GeniusPay veut l'international. Le plan de numérotation ivoirien à
     * 10 chiffres conserve son zéro initial derrière l'indicatif.
     */
    public static function toInternational(?string $phone): ?string
    {
        if ($phone === null || trim($phone) === '') {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $phone) ?? '';

        if ($digits === '') {
            return null;
        }

        return str_starts_with($digits, '225') ? '+' . $digits : '+225' . $digits;
    }
}
