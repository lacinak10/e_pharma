<?php

namespace Tests\Feature\Payment;

use App\Enums\PaymentStatus;
use App\Models\Payment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Le webhook est la seule porte ouverte sans authentification de session :
 * signature, fraîcheur, environnement, montant et rejeu y sont vérifiés.
 */
class GeniusPayWebhookTest extends TestCase
{
    use RefreshDatabase;

    private const SECRET = 'whsec_sandbox_test';

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.geniuspay.webhook_secret' => self::SECRET,
            'services.geniuspay.environment'    => 'sandbox',
        ]);
    }

    public function test_un_webhook_signe_encaisse_le_paiement(): void
    {
        $payment = Payment::factory()->create(['amount' => 15000]);

        $this->sendWebhook($this->payload($payment))->assertOk();

        $payment->refresh();

        $this->assertSame(PaymentStatus::COMPLETED, $payment->status);
        $this->assertNotNull($payment->paid_at);
        $this->assertSame('wave', $payment->method);
        $this->assertTrue($payment->order->isPaid());
    }

    public function test_une_signature_invalide_est_rejetee_sans_rien_ecrire(): void
    {
        $payment = Payment::factory()->create(['amount' => 15000]);

        $raw = json_encode($this->payload($payment));

        $this->call('POST', '/api/webhooks/geniuspay', [], [], [], [
            'CONTENT_TYPE'             => 'application/json',
            'HTTP_X_WEBHOOK_SIGNATURE' => str_repeat('0', 64),
            'HTTP_X_WEBHOOK_TIMESTAMP' => (string) time(),
            'HTTP_X_WEBHOOK_EVENT'     => 'payment.success',
        ], $raw)->assertUnauthorized();

        $this->assertSame(PaymentStatus::PENDING, $payment->refresh()->status);
        $this->assertDatabaseCount('payment_webhook_events', 0);
    }

    public function test_un_horodatage_perime_est_refuse(): void
    {
        $payment = Payment::factory()->create(['amount' => 15000]);

        $this->sendWebhook($this->payload($payment), timestamp: time() - 3600)
            ->assertStatus(400);

        $this->assertSame(PaymentStatus::PENDING, $payment->refresh()->status);
    }

    public function test_un_webhook_rejoue_n_agit_qu_une_seule_fois(): void
    {
        $payment = Payment::factory()->create(['amount' => 15000]);
        $payload = $this->payload($payment);

        $this->sendWebhook($payload)->assertOk();
        $eventsAfterFirst = $payment->order->events()->count();

        $this->sendWebhook($payload)->assertOk();

        $this->assertDatabaseCount('payment_webhook_events', 1);
        $this->assertSame($eventsAfterFirst, $payment->order->fresh()->events()->count());
    }

    public function test_un_montant_divergent_n_encaisse_pas(): void
    {
        $payment = Payment::factory()->create(['amount' => 15000]);

        $payload                   = $this->payload($payment);
        $payload['data']['amount'] = 500.00;

        $this->sendWebhook($payload)->assertOk();

        $this->assertSame(PaymentStatus::PENDING, $payment->refresh()->status);
    }

    public function test_un_webhook_d_un_autre_environnement_est_ignore(): void
    {
        $payment = Payment::factory()->create(['amount' => 15000]);

        $payload                        = $this->payload($payment);
        $payload['environment']         = 'live';
        $payload['data']['environment'] = 'live';

        $this->sendWebhook($payload)->assertOk();

        $this->assertSame(PaymentStatus::PENDING, $payment->refresh()->status);
    }

    public function test_une_reference_inconnue_repond_200_sans_redelivrance(): void
    {
        $payment = Payment::factory()->create(['amount' => 15000]);

        $payload                      = $this->payload($payment);
        $payload['data']['reference'] = 'MTX-INCONNUE1';

        $this->sendWebhook($payload)->assertOk();

        $this->assertSame(PaymentStatus::PENDING, $payment->refresh()->status);
    }

    public function test_un_paiement_encaisse_ne_redescend_jamais(): void
    {
        $payment = Payment::factory()->completed()->create(['amount' => 15000]);

        $payload                     = $this->payload($payment);
        $payload['event']            = 'payment.failed';
        $payload['data']['status']   = 'failed';

        $this->sendWebhook($payload, event: 'payment.failed')->assertOk();

        $this->assertSame(PaymentStatus::COMPLETED, $payment->refresh()->status);
    }

    public function test_un_remboursement_est_applique_sur_un_paiement_encaisse(): void
    {
        $payment = Payment::factory()->completed()->create(['amount' => 15000]);

        $payload                   = $this->payload($payment);
        $payload['event']          = 'payment.refunded';
        $payload['data']['status'] = 'refunded';

        $this->sendWebhook($payload, event: 'payment.refunded')->assertOk();

        $this->assertSame(PaymentStatus::REFUNDED, $payment->refresh()->status);
    }

    // ── Outillage ────────────────────────────────────────────────────────

    /** @return array<string, mixed> */
    private function payload(Payment $payment): array
    {
        return [
            'id'         => (string) Str::uuid(),
            'event'      => 'payment.success',
            'timestamp'  => time(),
            'created_at' => now()->toIso8601String(),
            'data'       => [
                'object'         => 'transaction',
                'id'             => 12345,
                'reference'      => $payment->reference,
                // GeniusPay sérialise le montant en flottant.
                'amount'         => (float) $payment->amount,
                'currency'       => 'XOF',
                'status'         => 'completed',
                'payment_method' => 'wave',
                'environment'    => 'sandbox',
                'metadata'       => ['order_id' => (string) $payment->order_id],
            ],
            'environment' => 'sandbox',
            'api_version' => '2024-01-01',
        ];
    }

    /**
     * Signe et poste le corps brut : la signature porte sur les octets envoyés,
     * pas sur un ré-encodage.
     *
     * @param  array<string, mixed>  $payload
     */
    private function sendWebhook(array $payload, ?int $timestamp = null, string $event = 'payment.success')
    {
        $raw       = json_encode($payload);
        $timestamp = (string) ($timestamp ?? time());

        return $this->call('POST', '/api/webhooks/geniuspay', [], [], [], [
            'CONTENT_TYPE'             => 'application/json',
            'HTTP_X_WEBHOOK_SIGNATURE' => hash_hmac('sha256', $timestamp . '.' . $raw, self::SECRET),
            'HTTP_X_WEBHOOK_TIMESTAMP' => $timestamp,
            'HTTP_X_WEBHOOK_EVENT'     => $event,
        ], $raw);
    }
}
