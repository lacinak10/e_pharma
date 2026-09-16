<?php

namespace App\Console\Commands;

use App\Models\Payment;
use App\Services\GeniusPay\GeniusPayException;
use App\Services\GeniusPay\PaymentReconciler;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Rattrape les paiements dont le webhook ne nous est jamais parvenu.
 *
 * Un webhook se perd — coupure réseau, déploiement en cours, 500 passager.
 * Sans ce filet, la commande resterait indéfiniment « en attente de paiement »
 * alors que le client a réglé, et le manager ne pourrait pas attribuer de
 * livreur. C'est aussi le seul moyen de voir aboutir un paiement en
 * développement, où aucun webhook n'atteint la machine locale.
 */
class ReconcilePayments extends Command
{
    protected $signature = 'epharma:reconcile-payments
                            {--minutes=3 : Ancienneté minimale d\'un paiement avant relecture}
                            {--limit=100 : Nombre maximum de paiements traités}';

    protected $description = 'Relit chez GeniusPay les paiements restés sans issue connue';

    public function handle(PaymentReconciler $reconciler): int
    {
        $payments = Payment::awaiting()
            ->where('created_at', '<=', now()->subMinutes((int) $this->option('minutes')))
            ->orderBy('id')
            ->limit((int) $this->option('limit'))
            ->get();

        if ($payments->isEmpty()) {
            $this->info('Aucun paiement à réconcilier.');

            return self::SUCCESS;
        }

        $changed = 0;
        $failed  = 0;

        foreach ($payments as $payment) {
            $before = $payment->status;

            try {
                $after = $reconciler->reconcile($payment)->status;
            } catch (GeniusPayException $e) {
                $failed++;

                Log::channel('geniuspay')->error('Réconciliation impossible', [
                    'reference' => $payment->reference,
                    'code'      => $e->errorCode,
                ]);

                $this->warn("{$payment->reference} : {$e->errorCode}");

                continue;
            }

            if ($after !== $before) {
                $changed++;
                $this->line("{$payment->reference} : {$before->value} → {$after->value}");
            }
        }

        $this->info("{$payments->count()} paiement(s) relu(s), {$changed} mis à jour, {$failed} en erreur.");

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }
}
