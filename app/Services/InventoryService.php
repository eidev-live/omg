<?php

namespace App\Services;

use App\Enums\StockMovementType;
use App\Exceptions\InsufficientStockException;
use App\Models\InventoryLayer;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;

class InventoryService
{
    public function __construct(private readonly FifoCostingService $fifo) {}

    /**
     * Stok berjalan dihitung dari seluruh pergerakan stok.
     */
    public function currentStock(): int
    {
        return (int) StockMovement::query()->sum('quantity');
    }

    /**
     * Stok dari sisa inventory layer.
     */
    public function layersRemaining(): int
    {
        return (int) InventoryLayer::query()->sum('quantity_remaining');
    }

    /**
     * Kedua sumber stok harus selalu konsisten.
     */
    public function isConsistent(): bool
    {
        return $this->currentStock() === $this->layersRemaining();
    }

    /**
     * Nilai persediaan dihitung dari sisa layer (FIFO), bukan harga beli terakhir.
     */
    public function inventoryValue(): float
    {
        return (float) InventoryLayer::query()
            ->selectRaw('COALESCE(SUM(quantity_remaining * unit_cost), 0) as value')
            ->value('value');
    }

    /**
     * Penyesuaian stok. Tidak mengubah histori pembelian/penjualan, tetapi tetap
     * menjaga konsistensi dengan inventory layer.
     */
    public function adjust(StockMovementType $type, int $quantity, string $reason): StockMovement
    {
        if (! in_array($type, [StockMovementType::AdjustmentIn, StockMovementType::AdjustmentOut], true)) {
            throw new InvalidArgumentException('Jenis penyesuaian stok tidak valid.');
        }

        if ($quantity <= 0) {
            throw new InvalidArgumentException('Jumlah penyesuaian harus lebih dari 0.');
        }

        return DB::transaction(function () use ($type, $quantity, $reason) {
            if ($type === StockMovementType::AdjustmentIn) {
                $this->addAdjustmentLayer($quantity);
            } else {
                $this->consumeAdjustmentStock($quantity);
            }

            return StockMovement::query()->create([
                'movement_date' => now()->toDateString(),
                'movement_type' => $type,
                'quantity' => $type === StockMovementType::AdjustmentIn ? $quantity : -$quantity,
                'reference_type' => 'ADJUSTMENT',
                'reference_id' => null,
                'notes' => $reason,
                'created_by' => auth()->id(),
            ]);
        });
    }

    private function addAdjustmentLayer(int $quantity): void
    {
        $unitCost = (float) (InventoryLayer::query()->orderByDesc('id')->value('unit_cost') ?? 0);

        InventoryLayer::query()->create([
            'purchase_id' => null,
            'quantity_received' => $quantity,
            'quantity_remaining' => $quantity,
            'unit_cost' => $unitCost,
        ]);
    }

    private function consumeAdjustmentStock(int $quantity): void
    {
        try {
            $this->fifo->consume($quantity);
        } catch (InsufficientStockException $exception) {
            throw ValidationException::withMessages([
                'quantity' => $exception->getMessage(),
            ]);
        }
    }
}
