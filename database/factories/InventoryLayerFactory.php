<?php

namespace Database\Factories;

use App\Models\InventoryLayer;
use App\Models\Purchase;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<InventoryLayer>
 */
class InventoryLayerFactory extends Factory
{
    public function definition(): array
    {
        $received = 180;

        return [
            'purchase_id' => Purchase::factory(),
            'quantity_received' => $received,
            'quantity_remaining' => $received,
            'unit_cost' => 2500,
        ];
    }
}
