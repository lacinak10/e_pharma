<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\GeniusPay\GeniusPayException;
use App\Services\GeniusPay\PaymentReconciler;
use App\Services\OrderWorkflow;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;

/**
 * Règlement en ligne d'une commande dont le verdict est rendu.
 */
class PaymentController extends Controller
{
    public function __construct(
        private OrderWorkflow $workflow,
        private PaymentReconciler $reconciler,
    ) {
    }

    /** Envoie le client sur la page de paiement hébergée par GeniusPay. */
    public function pay(Order $order): RedirectResponse
    {
        $this->authorize('pay', $order);

        try {
            $payment = $this->workflow->requestPayment($order);
        } catch (GeniusPayException $e) {
            Log::channel('geniuspay')->error('Paiement impossible à initier', [
                'order_id' => $order->id,
                'code'     => $e->errorCode,
            ]);

            return back()->with('error', "Le paiement en ligne est momentanément indisponible. Réessayez dans un instant ou contactez-nous.");
        }

        if ($payment === null || $payment->checkout_url === null) {
            return back()->with('error', 'Aucun lien de paiement disponible pour cette commande.');
        }

        if ($payment->isCompleted()) {
            return back()->with('success', 'Cette commande est déjà réglée.');
        }

        return redirect()->away($payment->checkout_url);
    }

    /**
     * Retour du client depuis GeniusPay (success_url et error_url).
     *
     * On ne se fie jamais à cette redirection pour marquer un paiement : elle
     * est déclenchée par le navigateur du client, donc falsifiable. On relit
     * l'état auprès de GeniusPay et on le passe par le même chemin idempotent
     * que le webhook — ce qui rend aussi le parcours testable en local, où
     * aucun webhook n'atteint la machine de développement.
     */
    public function return(Order $order): RedirectResponse
    {
        $this->authorize('view', $order);

        $payment = $order->payment;

        if ($payment === null) {
            return redirect()->route('store.orders.show', $order);
        }

        try {
            $payment = $this->reconciler->reconcile($payment, source: 'retour_client');
        } catch (GeniusPayException $e) {
            Log::channel('geniuspay')->warning('Relecture du paiement impossible au retour', [
                'reference' => $payment->reference,
                'code'      => $e->errorCode,
            ]);

            return redirect()
                ->route('store.orders.show', $order)
                ->with('info', 'Nous confirmons votre paiement, cela peut prendre quelques instants.');
        }

        return redirect()
            ->route('store.orders.show', $order)
            ->with(
                $payment->isCompleted() ? 'success' : 'info',
                $payment->status->label(),
            );
    }
}
