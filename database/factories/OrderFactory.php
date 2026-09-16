<?php

namespace Database\Factories;

use App\Enums\OrderStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    public function definition(): array
    {
        $subtotal = $this->faker->numberBetween(2000, 40000);

        return [
            'user_id'          => User::factory()->state(['role' => User::ROLE_CLIENT]),
            'status'           => OrderStatus::PENDING_VALIDATION,
            'delivery_address' => $this->faker->streetName() . ', Abidjan',
            'delivery_phone'   => '07' . $this->faker->numerify('########'),
            'notes'            => null,
            'payment_method'   => 'cash',
            'subtotal'         => $subtotal,
            'delivery_fee'     => 1500,
            'total_amount'     => $subtotal + 1500,
        ];
    }

    /** Commande en cours de vérification, chrono armé. */
    public function checking(): static
    {
        return $this->state(fn () => [
            'status'              => OrderStatus::CHECKING,
            'validated_at'        => now(),
            'checking_started_at' => now(),
            'check_deadline_at'   => now()->addSeconds(300),
        ]);
    }

    /** Verdict rendu, en attente d'un livreur. */
    public function available(): static
    {
        return $this->state(fn () => [
            'status'     => OrderStatus::AVAILABLE,
            'verdict_at' => now(),
        ]);
    }

    /** Commande à régler en ligne (Mobile Money ou carte). */
    public function online(string $method = 'momo'): static
    {
        return $this->state(fn () => ['payment_method' => $method]);
    }
}
