<?php

namespace App\Http\Controllers\Webhooks;

use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\PaymentWebhookEvent;
use App\Services\GeniusPay\PaymentReconciler;
use App\Services\GeniusPay\WebhookSignature;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Réception des webhooks GeniusPay.
 *
 * Hors du groupe « web » : ni session, ni CSRF. L'appelant est authentifié par
 * la signature HMAC de son payload, rien d'autre.
 *
 * Règle de sortie : on répond 2xx dès que le message a été compris, même si
 * l'événement ne nous concerne pas. Un 4xx/5xx déclenche des redélivraisons en
 * boucle chez GeniusPay pour un problème qui n'est pas le sien.
 */
class GeniusPayController extends Controller
{
    /** Sort d'un paiement déduit du type d'événement, si le payload ne le porte pas. */
    private const EVENT_STATUS = [
        'payment.initiated' => PaymentStatus::PENDING,
        'payment.success'   => PaymentStatus::COMPLETED,
        'payment.failed'    => PaymentStatus::FAILED,
        'payment.cancelled' => PaymentStatus::CANCELLED,
        'payment.expired'   => PaymentStatus::EXPIRED,
        'payment.refunded'  => PaymentStatus::REFUNDED,
    ];

    public function __construct(private WebhookSignature $signature, private PaymentReconciler $reconciler)
    {
    }

    public function __invoke(Request $request): JsonResponse
    {
        $secret = config('services.geniuspay.webhook_secret');

        if (empty($secret)) {
            Log::channel('geniuspay')->error('Webhook reçu sans secret configuré côté ePharma.');

            return response()->json(['status' => 500, 'detail' => 'Webhook secret not configured'], 500);
        }

        $timestamp = (string) $request->header('X-Webhook-Timestamp', '');
        $payload   = $request->json()->all();

        $valid = $this->signature->verify(
            $request->getContent(),
            $timestamp,
            (string) $request->header('X-Webhook-Signature', ''),
            $secret,
        );

        if (! $valid) {
            Log::channel('geniuspay')->warning('Webhook à signature invalide rejeté.', [
                'event' => $request->header('X-Webhook-Event'),
                'ip'    => $request->ip(),
            ]);

            return response()->json(['status' => 401, 'detail' => 'Invalid signature'], 401);
        }

        if (! $this->signature->timestampIsFresh($timestamp)) {
            return response()->json(['status' => 400, 'detail' => 'Timestamp too old'], 400);
        }

        $eventId = (string) ($payload['id'] ?? '');
        $event   = (string) ($payload['event'] ?? $request->header('X-Webhook-Event', ''));
        $data    = $payload['data'] ?? [];

        if ($eventId === '' || $event === '') {
            return response()->json(['status' => 400, 'detail' => 'Malformed payload'], 400);
        }

        // Déduplication : l'unicité en base est le verrou, pas un SELECT
        // préalable qui laisserait passer deux livraisons simultanées.
        try {
            PaymentWebhookEvent::create([
                'event_id'    => $eventId,
                'event'       => $event,
                'reference'   => $data['reference'] ?? null,
                'received_at' => now(),
            ]);
        } catch (UniqueConstraintViolationException) {
            return response()->json(['status' => 200, 'detail' => 'Already processed']);
        }

        Log::channel('geniuspay')->info('Webhook reçu', [
            'event'     => $event,
            'reference' => $data['reference'] ?? null,
        ]);

        if (! str_starts_with($event, 'payment.')) {
            return response()->json(['status' => 200, 'detail' => 'Ignored']);
        }

        $payment = Payment::where('reference', $data['reference'] ?? '')->first();

        if ($payment === null) {
            // Transaction inconnue : paiement d'un autre système, ou lien créé
            // puis perdu. Rien à appliquer, mais rien à redélivrer non plus.
            Log::channel('geniuspay')->warning('Webhook sur une référence inconnue', [
                'reference' => $data['reference'] ?? null,
            ]);

            return response()->json(['status' => 200, 'detail' => 'Unknown reference']);
        }

        // Le payload fait foi ; le type d'événement ne sert qu'à combler un
        // « status » absent.
        if (! isset($data['status']) && isset(self::EVENT_STATUS[$event])) {
            $data['status'] = self::EVENT_STATUS[$event]->value;
        }

        $this->reconciler->apply($payment, $data, source: 'webhook');

        return response()->json(['status' => 200, 'detail' => 'Processed']);
    }
}
