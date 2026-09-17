<?php

namespace Database\Factories;

use App\Enums\TransactionStatus;
use App\Models\Purchase;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Purchase>
 */
class PurchaseFactory extends Factory
{
    public function definition(): array
    {
        $eggQuantity = 180;
        $totalCost = 475000;

        return [
            'purchase_number' => 'PUR-'.now()->format('Ymd').'-'.fake()->unique()->numerify('####'),
            'purchase_date' => now()->toDateString(),
            'quantity' => 1,
            'egg_quantity' => $eggQuantity,
            'total_cost' => $totalCost,
            'cost_per_egg' => $totalCost / $eggQuantity,
            'notes' => null,
            'status' => TransactionStatus::Active,
        ];
    }
}
