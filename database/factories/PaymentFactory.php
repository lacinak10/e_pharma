<?php

namespace Database\Factories;

use App\Enums\PaymentStatus;
use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Payment>
 */
class PaymentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'order_id'     => Order::factory()->online()->available(),
            'provider'     => 'geniuspay',
            // Format réellement émis par le sandbox GeniusPay — leur
            // documentation annonce « MTX-… », ce n'est pas ce qui arrive.
            'reference'    => 'SANDBOX_' . Str::upper(Str::random(16)),
            'status'       => PaymentStatus::PENDING,
            'amount'       => $this->faker->numberBetween(2000, 50000),
            'currency'     => 'XOF',
            'environment'  => 'sandbox',
            'checkout_url' => 'https://geniuspay.ci/checkout/MTX-TEST',
            'expires_at'   => now()->addDay(),
        ];
    }

    public function completed(): static
    {
        return $this->state(fn () => [
            'status'  => PaymentStatus::COMPLETED,
            'method'  => 'wave',
            'paid_at' => now(),
        ]);
    }
}
