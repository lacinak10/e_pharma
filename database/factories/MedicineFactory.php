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
        $stock     = $this->faker->numberBetween(0, 200);
        $threshold = $this->faker->numberBetween(5, 30);

        if ($stock <= 0) {
            $status = 'Épuisé';
        } elseif ($stock <= $threshold) {
            $status = 'Stock faible';
        } else {
            $status = 'En stock';
        }

        return [
            'category_id'     => Category::inRandomOrder()->value('id'),
            'name'            => $this->faker->unique()->words(3, true),
            'description'     => $this->faker->paragraph(),
            'price'           => $this->faker->numberBetween(500, 50000),
            'stock'           => $stock,
            'alert_threshold' => $threshold,
            'status'          => $status,
            'reference'       => 'MED-' . str_pad(self::$refCounter++, 5, '0', STR_PAD_LEFT),
            'image_url'       => null,
            'is_active'       => $this->faker->boolean(90),
        ];
    }
}
