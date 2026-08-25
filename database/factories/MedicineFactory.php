<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Medicine>
 */
class MedicineFactory extends Factory
{
    private static int $refCounter = 1;

    public function definition(): array
    {
        return [
            'category_id'     => Category::inRandomOrder()->value('id'),
            'name'            => $this->faker->unique()->words(3, true),
            'description'     => $this->faker->paragraph(),
            'indication'      => $this->faker->sentence(4),
            'dosage'          => $this->faker->numberBetween(50, 1000) . ' mg',
            'pack'            => $this->faker->numberBetween(6, 30) . ' comprimés',
            'requires_prescription' => $this->faker->boolean(30),
            'price'           => $this->faker->numberBetween(500, 50000),
            'reference'       => 'MED-' . str_pad(self::$refCounter++, 5, '0', STR_PAD_LEFT),
            'image_url'       => null,
            'is_active'       => $this->faker->boolean(90),
        ];
    }
}
