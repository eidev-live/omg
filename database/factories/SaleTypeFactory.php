<?php

namespace Database\Factories;

use App\Models\SaleType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SaleType>
 */
class SaleTypeFactory extends Factory
{
    public function definition(): array
    {
        $eggQuantity = fake()->randomElement([10, 30, 180]);

        return [
            'name' => fake()->randomElement(['Pack', 'Tray', 'Ikat']).' '.fake()->unique()->numberBetween(1, 99999),
            'egg_quantity' => $eggQuantity,
            'selling_price' => fake()->numberBetween(10, 60) * 1000,
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
