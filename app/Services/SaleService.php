<?php

namespace App\Services;

use App\Enums\DeliveryStatus;
use App\Enums\PaymentStatus;
use App\Enums\StockMovementType;
use App\Enums\TransactionStatus;
use App\Models\Customer;
use App\Models\Sale;
use App\Models\SaleType;
use App\Support\DocumentNumber;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SaleService
{
    public function __construct(private readonly FifoCostingService $fifo) {}

    /**
     * @param  array{
     *     sale_date: string,
     *     customer_id: int,
     *     items: array<int, array{sale_type_id: int, quantity: int}>,
     *     paid_amount?: int|null,
     *     payment_date?: string|null,
     *     delivery_status: string,
     *     notes?: string|null
     * }  $data
     */
    public function create(array $data): Sale
    {
        return DB::transaction(function () use ($data) {
            $customer = Customer::query()->find($data['customer_id']);

            if (! $customer || ! $customer->is_active) {
                throw ValidationException::withMessages(['customer_id' => 'Customer tidak valid atau sedang nonaktif.']);
            }

            $lines = $this->buildLines($data['items']);
            $totalEggs = array_sum(array_column($lines, 'egg_quantity'));
            $totalAmount = array_sum(array_column($lines, 'subtotal'));

            $available = $this->fifo->availableQuantity();

            if ($available < $totalEggs) {
                throw ValidationException::withMessages([
                    'items' => "Stok telur tidak mencukupi. Stok tersedia {$available} butir, kebutuhan {$totalEggs} butir.",
                ]);
            }

            $paidAmount = min((int) ($data['paid_amount'] ?? 0), $totalAmount);

            if ((int) ($data['paid_amount'] ?? 0) > $totalAmount) {
                throw ValidationException::withMessages([
                    'paid_amount' => 'Jumlah dibayar tidak boleh melebihi total transaksi.',
                ]);
            }

            $deliveryStatus = DeliveryStatus::from($data['delivery_status']);

            $sale = Sale::query()->create([
                'invoice_number' => DocumentNumber::next('sales', 'invoice_number', 'INV'),
                'sale_date' => $data['sale_date'],
                'customer_id' => $customer->id,
                'total_amount' => $totalAmount,
                'payment_status' => $this->paymentStatus($paidAmount, $totalAmount),
                'paid_amount' => $paidAmount,
                'payment_date' => $paidAmount > 0 ? ($data['payment_date'] ?? now()->toDateString()) : null,
                'delivery_status' => $deliveryStatus,
                'delivered_at' => $deliveryStatus === DeliveryStatus::Delivered ? now() : null,
                'notes' => $data['notes'] ?? null,
                'status' => TransactionStatus::Active,
                'created_by' => auth()->id(),
                'updated_by' => auth()->id(),
            ]);

            foreach ($lines as $line) {
                $result = $this->fifo->consume($line['egg_quantity']);
                $totalCost = $result['total_cost'];

                $item = $sale->items()->create([
                    'sale_type_id' => $line['sale_type_id'],
                    'sale_type_name' => $line['sale_type_name'],
                    'quantity' => $line['quantity'],
                    'egg_quantity' => $line['egg_quantity'],
                    'unit_price' => $line['unit_price'],
                    'subtotal' => $line['subtotal'],
                    'unit_cost' => $line['egg_quantity'] > 0 ? $totalCost / $line['egg_quantity'] : 0,
                    'total_cost' => $totalCost,
                    'profit' => $line['subtotal'] - $totalCost,
                ]);

                $item->consumptions()->createMany($result['consumptions']);
            }

            $sale->stockMovements()->create([
                'movement_date' => $data['sale_date'],
                'movement_type' => StockMovementType::Sale,
                'quantity' => -$totalEggs,
                'notes' => "Penjualan {$sale->invoice_number}",
                'created_by' => auth()->id(),
            ]);

            return $sale;
        });
    }

    public function cancel(Sale $sale, ?string $reason = null): Sale
    {
        if ($sale->isCancelled()) {
            throw ValidationException::withMessages(['sale' => 'Transaksi ini sudah dibatalkan sebelumnya.']);
        }

        return DB::transaction(function () use ($sale, $reason) {
            $sale->load('items.consumptions');

            $consumptions = $sale->items
                ->flatMap(fn ($item) => $item->consumptions)
                ->map(fn ($consumption) => [
                    'inventory_layer_id' => $consumption->inventory_layer_id,
                    'quantity' => $consumption->quantity,
                ])
                ->all();

            $this->fifo->restore($consumptions);

            $totalEggs = (int) $sale->items->sum('egg_quantity');

            $sale->update([
                'status' => TransactionStatus::Cancelled,
                'cancelled_at' => now(),
                'cancel_reason' => $reason,
                'updated_by' => auth()->id(),
            ]);

            $sale->stockMovements()->create([
                'movement_date' => now()->toDateString(),
                'movement_type' => StockMovementType::SaleCancel,
                'quantity' => $totalEggs,
                'notes' => "Pembatalan penjualan {$sale->invoice_number}",
                'created_by' => auth()->id(),
            ]);

            return $sale;
        });
    }

    public function updatePayment(Sale $sale, int $paidAmount, ?string $paymentDate = null): Sale
    {
        $this->ensureEditable($sale);

        if ($paidAmount > $sale->total_amount) {
            throw ValidationException::withMessages([
                'paid_amount' => 'Jumlah dibayar tidak boleh melebihi total transaksi.',
            ]);
        }

        $sale->update([
            'paid_amount' => $paidAmount,
            'payment_status' => $this->paymentStatus($paidAmount, $sale->total_amount),
            'payment_date' => $paidAmount > 0 ? ($paymentDate ?? now()->toDateString()) : null,
            'updated_by' => auth()->id(),
        ]);

        return $sale;
    }

    public function updateDelivery(Sale $sale, DeliveryStatus $status): Sale
    {
        $this->ensureEditable($sale);

        $sale->update([
            'delivery_status' => $status,
            'delivered_at' => $status === DeliveryStatus::Delivered ? ($sale->delivered_at ?? now()) : null,
            'updated_by' => auth()->id(),
        ]);

        return $sale;
    }

    private function ensureEditable(Sale $sale): void
    {
        if ($sale->isCancelled()) {
            throw ValidationException::withMessages([
                'sale' => 'Transaksi yang sudah dibatalkan tidak dapat diubah.',
            ]);
        }
    }

    /**
     * @param  array<int, array{sale_type_id: int, quantity: int}>  $items
     * @return array<int, array{sale_type_id: int, sale_type_name: string, quantity: int, egg_quantity: int, unit_price: int, subtotal: int}>
     */
    private function buildLines(array $items): array
    {
        $lines = [];

        foreach ($items as $item) {
            $saleType = SaleType::query()->find($item['sale_type_id']);

            if (! $saleType || ! $saleType->is_active) {
                throw ValidationException::withMessages(['items' => 'Tipe penjualan tidak valid atau sedang nonaktif.']);
            }

            $quantity = (int) $item['quantity'];

            $lines[] = [
                'sale_type_id' => $saleType->id,
                'sale_type_name' => $saleType->name,
                'quantity' => $quantity,
                'egg_quantity' => $saleType->egg_quantity * $quantity,
                'unit_price' => $saleType->selling_price,
                'subtotal' => $saleType->selling_price * $quantity,
            ];
        }

        return $lines;
    }

    private function paymentStatus(int $paidAmount, int $totalAmount): PaymentStatus
    {
        if ($paidAmount <= 0) {
            return PaymentStatus::Unpaid;
        }

        if ($paidAmount >= $totalAmount) {
            return PaymentStatus::Paid;
        }

        return PaymentStatus::Partial;
    }
}
