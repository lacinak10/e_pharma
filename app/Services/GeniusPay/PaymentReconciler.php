<?php

namespace App\Services\GeniusPay;

use App\Enums\PaymentStatus;
use App\Models\Payment;
use App\Services\OrderWorkflow;
use Illuminate\Support\Facades\Log;

/**
 * Applique à un paiement local l'état que GeniusPay lui connaît.
 *
 * Point de passage commun au webhook, à la page de retour du client et à la
 * commande de réconciliation : les trois sources aboutissent aux mêmes
 * contrôles, donc aux mêmes décisions.
 */
class PaymentReconciler
{
    public function __construct(
        private GeniusPayClient $client,
        private OrderWorkflow $workflow,
    ) {
    }

    /**
     * Relit la transaction chez GeniusPay et applique son état.
     *
     * @throws GeniusPayException
     */
    public function reconcile(Payment $payment, string $source = 'reconcile'): Payment
    {
        return $this->apply($payment, $this->client->fetchPayment($payment->reference), $source);
    }

    /**
     * Applique un état déjà obtenu (payload de webhook ou réponse GET).
     *
     * @param  array<string, mixed>  $data  l'objet transaction de GeniusPay
     */
    public function apply(Payment $payment, array $data, string $source): Payment
    {
        $status = PaymentStatus::tryFrom((string) ($data['status'] ?? ''));

        if ($status === null) {
            Log::channel('geniuspay')->warning('Statut inconnu, paiement laissé en l\'état', [
                'reference' => $payment->reference,
                'status'    => $data['status'] ?? null,
                'source'    => $source,
            ]);

            return $payment;
        }

        /*
         * Un paiement sandbox ne doit jamais créditer une commande live, ni
         * l'inverse. GeniusPay renvoie l'environnement dans chaque payload :
         * on refuse tout ce qui ne correspond pas à notre configuration.
         */
        $environment = $data['environment'] ?? null;

        if ($environment !== null && $environment !== config('services.geniuspay.environment')) {
            Log::channel('geniuspay')->error('Environnement discordant, paiement refusé', [
                'reference' => $payment->reference,
                'recu'      => $environment,
                'attendu'   => config('services.geniuspay.environment'),
                'source'    => $source,
            ]);

            return $payment;
        }

        /*
         * Le montant est comparé à celui figé à la création du lien, jamais à
         * `orders.total_amount` : si les deux divergent, c'est précisément ce
         * qu'il faut détecter plutôt que masquer.
         */
        if ($status === PaymentStatus::COMPLETED && ! $this->amountMatches($payment, $data)) {
            Log::channel('geniuspay')->error('Montant discordant, encaissement non appliqué', [
                'reference' => $payment->reference,
                'attendu'   => $payment->amount,
                'recu'      => $data['amount'] ?? null,
                'source'    => $source,
            ]);

            return $payment;
        }

        return $this->workflow->applyPaymentStatus($payment, $status, [
            'method'  => $data['payment_method'] ?? $data['provider'] ?? $data['payment_provider'] ?? null,
            'reason'  => $data['failure_reason'] ?? $data['message'] ?? null,
            'payload' => $data,
        ]);
    }

    /** @param  array<string, mixed>  $data */
    private function amountMatches(Payment $payment, array $data): bool
    {
        if (! isset($data['amount'])) {
            return false;
        }

        // Le webhook sérialise le montant en flottant (« 10000.00 »).
        return (int) round((float) $data['amount']) === $payment->amount;
    }
}
