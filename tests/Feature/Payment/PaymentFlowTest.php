<?php

namespace Tests\Feature\Payment;

use App\Enums\ItemAvailability;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Category;
use App\Models\Medicine;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use App\Services\OrderWorkflow;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

/**
 * Le lien de paiement naît au verdict, sur le montant réellement dû.
 */
class PaymentFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.geniuspay.key'            => 'pk_sandbox_test',
            'services.geniuspay.secret'         => 'sk_sandbox_test',
            'services.geniuspay.webhook_secret' => 'whsec_sandbox_test',
            'services.geniuspay.environment'    => 'sandbox',
        ]);
    }

    public function test_le_verdict_cree_un_lien_de_paiement_pour_une_commande_en_ligne(): void
    {
        $this->fakeCreatePayment();

        $order = $this->orderInChecking(paymentMethod: 'momo', lines: [
            [12000, ItemAvailability::AVAILABLE],
        ]);

        app(OrderWorkflow::class)->settleVerdict($order, $this->manager());

        $payment = $order->fresh()->payment;

        $this->assertNotNull($payment);
        $this->assertSame(PaymentStatus::PENDING, $payment->status);
        $this->assertSame(13500, $payment->amount, 'Le lien doit porter le total livraison comprise.');
        $this->assertSame('https://geniuspay.ci/checkout/MTX-TEST000001', $payment->checkout_url);
    }

    public function test_le_lien_porte_le_montant_reduit_apres_un_verdict_partiel(): void
    {
        $this->fakeCreatePayment();

        $order = $this->orderInChecking(paymentMethod: 'momo', lines: [
            [10000, ItemAvailability::AVAILABLE],
            [5000, ItemAvailability::UNAVAILABLE],
        ]);

        app(OrderWorkflow::class)->settleVerdict($order, $this->manager());

        $order = $order->fresh();

        $this->assertSame(OrderStatus::PARTIALLY_AVAILABLE, $order->status);
        $this->assertSame(11500, $order->total_amount);

        // C'est tout l'enjeu : on n'encaisse jamais la ligne introuvable.
        $this->assertSame(11500, $order->payment->amount);

        Http::assertSent(fn ($request) => $request['amount'] === 11500);
    }

    public function test_aucun_lien_n_est_cree_pour_un_paiement_en_especes(): void
    {
        Http::fake();

        $order = $this->orderInChecking(paymentMethod: 'cash', lines: [
            [8000, ItemAvailability::AVAILABLE],
        ]);

        app(OrderWorkflow::class)->settleVerdict($order, $this->manager());

        $this->assertNull($order->fresh()->payment);
        Http::assertNothingSent();
    }

    public function test_un_lien_valide_est_reutilise_plutot_que_duplique(): void
    {
        $this->fakeCreatePayment();

        $order = $this->orderInChecking(paymentMethod: 'momo', lines: [
            [12000, ItemAvailability::AVAILABLE],
        ]);

        $workflow = app(OrderWorkflow::class);
        $workflow->settleVerdict($order, $this->manager());
        $workflow->requestPayment($order->fresh());

        $this->assertSame(1, $order->payments()->count());
        Http::assertSentCount(1);
    }

    public function test_l_attribution_est_refusee_tant_que_le_paiement_n_est_pas_encaisse(): void
    {
        $this->fakeCreatePayment();

        $order = $this->orderInChecking(paymentMethod: 'momo', lines: [
            [12000, ItemAvailability::AVAILABLE],
        ]);

        $workflow = app(OrderWorkflow::class);
        $order    = $workflow->settleVerdict($order, $this->manager());

        $this->expectException(ValidationException::class);

        $workflow->assignCourier($order, $this->courier(), $this->manager());
    }

    public function test_l_attribution_passe_une_fois_le_paiement_encaisse(): void
    {
        $this->fakeCreatePayment();

        $order = $this->orderInChecking(paymentMethod: 'momo', lines: [
            [12000, ItemAvailability::AVAILABLE],
        ]);

        $workflow = app(OrderWorkflow::class);
        $order    = $workflow->settleVerdict($order, $this->manager());

        $workflow->applyPaymentStatus($order->payment, PaymentStatus::COMPLETED, ['method' => 'wave']);

        $order = $workflow->assignCourier($order->fresh(), $this->courier(), $this->manager());

        $this->assertSame(OrderStatus::COURIER_ASSIGNED, $order->status);
    }

    public function test_l_encaissement_journalise_un_evenement_et_notifie_le_client(): void
    {
        $this->fakeCreatePayment();

        $order = $this->orderInChecking(paymentMethod: 'momo', lines: [
            [12000, ItemAvailability::AVAILABLE],
        ]);

        $workflow = app(OrderWorkflow::class);
        $order    = $workflow->settleVerdict($order, $this->manager());

        $before = $order->events()->count();

        $workflow->applyPaymentStatus($order->payment, PaymentStatus::COMPLETED, ['method' => 'wave']);

        // Sans OrderEvent, la timeline client et le flux « Activité en direct »
        // resteraient muets sur l'encaissement.
        $this->assertSame($before + 1, $order->fresh()->events()->count());
        $this->assertDatabaseHas('notifications', [
            'type'            => \App\Notifications\PaymentStatusNotification::class,
            'notifiable_id'   => $order->user_id,
            'notifiable_type' => User::class,
        ]);
    }

    // ── Fabriques locales ────────────────────────────────────────────────

    /** Rejoue le comportement réel : GeniusPay réécho le montant demandé. */
    private function fakeCreatePayment(): void
    {
        Http::fake([
            '*/payments' => function ($request) {
                return Http::response([
                    'success' => true,
                    'data'    => [
                        'id'           => 1,
                        'reference'    => 'MTX-TEST000001',
                        'amount'       => $request['amount'],
                        'currency'     => 'XOF',
                        'status'       => 'pending',
                        'checkout_url' => 'https://geniuspay.ci/checkout/MTX-TEST000001',
                        'environment'  => 'sandbox',
                        'expires_at'   => now()->addDay()->toIso8601String(),
                    ],
                ], 201);
            },
        ]);
    }

    /** @param  array<int, array{0: int, 1: ItemAvailability}>  $lines */
    private function orderInChecking(string $paymentMethod, array $lines): Order
    {
        $order = Order::factory()->checking()->online($paymentMethod)->create();

        foreach ($lines as $i => [$price, $availability]) {
            OrderItem::create([
                'order_id'              => $order->id,
                'medicine_id'           => $this->medicine($price)->id,
                'medicine_name'         => "Médicament {$i}",
                'unit_price'            => $price,
                'quantity'              => 1,
                'line_total'            => $price,
                'availability'          => $availability,
                'requires_prescription' => false,
            ]);
        }

        return $order->fresh();
    }

    private function medicine(int $price): Medicine
    {
        $category = Category::firstOrCreate(
            ['slug' => 'test'],
            ['name' => 'Test', 'is_active' => true],
        );

        return Medicine::factory()->create([
            'category_id' => $category->id,
            'price'       => $price,
            'is_active'   => true,
        ]);
    }

    private function manager(): User
    {
        return User::factory()->create(['role' => User::ROLE_MANAGER]);
    }

    private function courier(): User
    {
        return User::factory()->create(['role' => User::ROLE_COURIER, 'is_active' => true]);
    }
}
