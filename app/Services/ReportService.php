<?php

namespace App\Services;

use App\Enums\StockMovementType;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\StockMovement;
use Illuminate\Database\Eloquent\Builder;

class ReportService
{
    public function __construct(
        private readonly InventoryService $inventory,
        private readonly ProfitService $profit,
    ) {}

    /**
     * @param  array<string, mixed>  $filters
     * @return Builder<Sale>
     */
    public function salesQuery(array $filters): Builder
    {
        return Sale::query()
            ->with('customer')
            ->when($filters['date_from'] ?? null, fn ($query, $value) => $query->whereDate('sale_date', '>=', $value))
            ->when($filters['date_to'] ?? null, fn ($query, $value) => $query->whereDate('sale_date', '<=', $value))
            ->when($filters['customer_id'] ?? null, fn ($query, $value) => $query->where('customer_id', $value))
            ->when($filters['payment'] ?? null, fn ($query, $value) => $query->where('payment_status', $value))
            ->when($filters['delivery'] ?? null, fn ($query, $value) => $query->where('delivery_status', $value))
            ->orderByDesc('sale_date')
            ->orderByDesc('id');
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array{revenue: int, paid: int, outstanding: int, count: int}
     */
    public function salesSummary(array $filters): array
    {
        return [
            'revenue' => (int) $this->salesQuery($filters)->sum('total_amount'),
            'paid' => (int) $this->salesQuery($filters)->sum('paid_amount'),
            'outstanding' => (int) $this->salesQuery($filters)
                ->selectRaw('COALESCE(SUM(total_amount - paid_amount), 0) as value')
                ->value('value'),
            'count' => $this->salesQuery($filters)->count(),
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return Builder<Purchase>
     */
    public function purchasesQuery(array $filters): Builder
    {
        return Purchase::query()
            ->when($filters['date_from'] ?? null, fn ($query, $value) => $query->whereDate('purchase_date', '>=', $value))
            ->when($filters['date_to'] ?? null, fn ($query, $value) => $query->whereDate('purchase_date', '<=', $value))
            ->orderByDesc('purchase_date')
            ->orderByDesc('id');
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array{total_cost: int, egg_quantity: int, count: int}
     */
    public function purchasesSummary(array $filters): array
    {
        return [
            'total_cost' => (int) $this->purchasesQuery($filters)->sum('total_cost'),
            'egg_quantity' => (int) $this->purchasesQuery($filters)->sum('egg_quantity'),
            'count' => $this->purchasesQuery($filters)->count(),
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array{totals: array<string, mixed>, series: array<int, array<string, mixed>>}
     */
    public function profitReport(array $filters): array
    {
        $from = $filters['date_from'] ?? null;
        $to = $filters['date_to'] ?? null;
        $customerId = $filters['customer_id'] ?? null;

        return [
            'totals' => $this->profit->summary($from, $to, $customerId),
            'series' => $this->profit->dailySeries($from, $to, $customerId)->all(),
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array{current: int, inventory_value: float, stock_in: int, stock_out: int, adjustment: int}
     */
    public function stockSummary(array $filters): array
    {
        $from = $filters['date_from'] ?? null;
        $to = $filters['date_to'] ?? null;

        $movements = fn () => StockMovement::query()
            ->when($from, fn ($query, $value) => $query->whereDate('movement_date', '>=', $value))
            ->when($to, fn ($query, $value) => $query->whereDate('movement_date', '<=', $value));

        $stockIn = (int) $movements()->whereIn('movement_type', [
            StockMovementType::Purchase->value,
            StockMovementType::AdjustmentIn->value,
        ])->sum('quantity');

        $stockOut = (int) $movements()->whereIn('movement_type', [
            StockMovementType::Sale->value,
            StockMovementType::AdjustmentOut->value,
        ])->sum('quantity');

        $adjustment = (int) $movements()->whereIn('movement_type', [
            StockMovementType::AdjustmentIn->value,
            StockMovementType::AdjustmentOut->value,
        ])->sum('quantity');

        return [
            'current' => $this->inventory->currentStock(),
            'inventory_value' => $this->inventory->inventoryValue(),
            'stock_in' => $stockIn,
            'stock_out' => abs($stockOut),
            'adjustment' => $adjustment,
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return Builder<StockMovement>
     */
    public function stockMovementsQuery(array $filters): Builder
    {
        return StockMovement::query()
            ->when($filters['date_from'] ?? null, fn ($query, $value) => $query->whereDate('movement_date', '>=', $value))
            ->when($filters['date_to'] ?? null, fn ($query, $value) => $query->whereDate('movement_date', '<=', $value))
            ->orderByDesc('movement_date')
            ->orderByDesc('id');
    }
}
