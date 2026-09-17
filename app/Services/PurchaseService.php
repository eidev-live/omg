<?php

namespace App\Services;

use App\Enums\StockMovementType;
use App\Enums\TransactionStatus;
use App\Models\Purchase;
use App\Support\DocumentNumber;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PurchaseService
{
    /**
     * @param  array{purchase_date: string, quantity: int, egg_per_unit: int, total_cost: int, notes?: string|null}  $data
     */
    public function create(array $data): Purchase
    {
        return DB::transaction(function () use ($data) {
            $eggQuantity = (int) $data['quantity'] * (int) $data['egg_per_unit'];
            $totalCost = (int) $data['total_cost'];
            $costPerEgg = $totalCost / $eggQuantity;

            $purchase = Purchase::query()->create([
                'purchase_number' => DocumentNumber::next('purchases', 'purchase_number', 'PUR'),
                'purchase_date' => $data['purchase_date'],
                'quantity' => (int) $data['quantity'],
                'egg_quantity' => $eggQuantity,
                'total_cost' => $totalCost,
                'cost_per_egg' => $costPerEgg,
                'notes' => $data['notes'] ?? null,
                'status' => TransactionStatus::Active,
                'created_by' => auth()->id(),
                'updated_by' => auth()->id(),
            ]);

            $purchase->layer()->create([
                'quantity_received' => $eggQuantity,
                'quantity_remaining' => $eggQuantity,
                'unit_cost' => $costPerEgg,
            ]);

            $this->recordMovement($purchase, StockMovementType::Purchase, $eggQuantity, (string) $data['purchase_date']);

            return $purchase;
        });
    }

    /**
     * Membatalkan pembelian dan mengembalikan stok. Hanya boleh bila layer belum terpakai.
     */
    public function cancel(Purchase $purchase, ?string $reason = null): Purchase
    {
        if ($purchase->isCancelled()) {
            throw ValidationException::withMessages([
                'purchase' => 'Pembelian ini sudah dibatalkan sebelumnya.',
            ]);
        }

        $layer = $purchase->layer;

        if ($layer && $layer->quantity_remaining !== $layer->quantity_received) {
            throw ValidationException::withMessages([
                'purchase' => 'Pembelian tidak dapat dibatalkan karena stoknya sudah terpakai pada penjualan.',
            ]);
        }

        return DB::transaction(function () use ($purchase, $layer, $reason) {
            if ($layer) {
                $layer->update(['quantity_remaining' => 0]);
            }

            $purchase->update([
                'status' => TransactionStatus::Cancelled,
                'cancelled_at' => now(),
                'cancel_reason' => $reason,
                'updated_by' => auth()->id(),
            ]);

            $this->recordMovement($purchase, StockMovementType::PurchaseCancel, -$purchase->egg_quantity, now()->toDateString());

            return $purchase;
        });
    }

    private function recordMovement(Purchase $purchase, StockMovementType $type, int $quantity, string $date): void
    {
        $label = $type === StockMovementType::Purchase ? 'Pembelian' : 'Pembatalan pembelian';

        $purchase->stockMovements()->create([
            'movement_date' => $date,
            'movement_type' => $type,
            'quantity' => $quantity,
            'notes' => "{$label} {$purchase->purchase_number}",
            'created_by' => auth()->id(),
        ]);
    }
}
