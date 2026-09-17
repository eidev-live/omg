<?php

namespace App\Services;

use App\Exceptions\InsufficientStockException;
use App\Models\InventoryLayer;
use InvalidArgumentException;

class FifoCostingService
{
    /**
     * Stok yang masih tersedia untuk dijual (dari seluruh layer).
     */
    public function availableQuantity(): int
    {
        return (int) InventoryLayer::query()
            ->where('quantity_remaining', '>', 0)
            ->sum('quantity_remaining');
    }

    public function canFulfill(int $requiredQuantity): bool
    {
        return $this->availableQuantity() >= $requiredQuantity;
    }

    /**
     * Mengambil stok dari layer paling lama (FIFO) dan mengurangi sisa layer.
     *
     * @return array{
     *     consumptions: array<int, array{inventory_layer_id: int, quantity: int, unit_cost: float, total_cost: float}>,
     *     total_cost: float,
     *     total_quantity: int
     * }
     *
     * @throws InsufficientStockException
     */
    public function consume(int $requiredQuantity): array
    {
        if ($requiredQuantity <= 0) {
            throw new InvalidArgumentException('Jumlah telur yang diambil harus lebih dari 0.');
        }

        $available = $this->availableQuantity();

        if ($available < $requiredQuantity) {
            throw new InsufficientStockException($available, $requiredQuantity);
        }

        $remaining = $requiredQuantity;
        $consumptions = [];
        $totalCost = 0.0;

        $layers = InventoryLayer::query()
            ->where('quantity_remaining', '>', 0)
            ->orderBy('created_at')
            ->orderBy('id')
            ->lockForUpdate()
            ->get();

        foreach ($layers as $layer) {
            if ($remaining <= 0) {
                break;
            }

            $take = min($layer->quantity_remaining, $remaining);
            $unitCost = (float) $layer->unit_cost;
            $lineCost = $take * $unitCost;

            $layer->quantity_remaining -= $take;
            $layer->save();

            $consumptions[] = [
                'inventory_layer_id' => $layer->id,
                'quantity' => $take,
                'unit_cost' => $unitCost,
                'total_cost' => $lineCost,
            ];

            $totalCost += $lineCost;
            $remaining -= $take;
        }

        return [
            'consumptions' => $consumptions,
            'total_cost' => $totalCost,
            'total_quantity' => $requiredQuantity,
        ];
    }

    /**
     * Mengembalikan stok ke layer asal (kebalikan dari consume), dipakai saat pembatalan penjualan.
     *
     * @param  array<int, array{inventory_layer_id: int, quantity: int}>  $consumptions
     */
    public function restore(array $consumptions): void
    {
        foreach ($consumptions as $consumption) {
            $layer = InventoryLayer::query()->lockForUpdate()->find($consumption['inventory_layer_id']);

            if ($layer === null) {
                continue;
            }

            $layer->quantity_remaining = min(
                $layer->quantity_received,
                $layer->quantity_remaining + (int) $consumption['quantity'],
            );
            $layer->save();
        }
    }
}
