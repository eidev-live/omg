<?php

namespace App\Services;

use App\Enums\DeliveryStatus;
use App\Enums\TransactionStatus;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\Setting;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class DashboardService
{
    public function __construct(
        private readonly InventoryService $inventory,
        private readonly ProfitService $profit,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function summary(string $dateFrom, string $dateTo): array
    {
        $sales = $this->activeSales($dateFrom, $dateTo);
        $profit = $this->profit->summary($dateFrom, $dateTo);

        $purchases = Purchase::query()
            ->where('status', TransactionStatus::Active->value)
            ->whereDate('purchase_date', '>=', $dateFrom)
            ->whereDate('purchase_date', '<=', $dateTo);

        $outstanding = Sale::query()
            ->where('status', TransactionStatus::Active->value)
            ->whereColumn('paid_amount', '<', 'total_amount');

        $stock = $this->inventory->currentStock();
        $minimumStock = Setting::minimumStock();

        return [
            'revenue' => (int) $sales->sum('total_amount'),
            'sales_count' => $sales->count(),
            'cash_in' => (int) $sales->sum('paid_amount'),
            'outstanding' => (int) (clone $outstanding)->selectRaw('COALESCE(SUM(total_amount - paid_amount), 0) as value')->value('value'),
            'outstanding_count' => (int) (clone $outstanding)->count(),
            'purchases_total' => (int) $purchases->sum('total_cost'),
            'purchases_count' => (int) $purchases->count(),
            'cogs' => $profit['cogs'],
            'gross_profit' => $profit['gross_profit'],
            'margin' => $profit['margin'],
            'stock' => $stock,
            'minimum_stock' => $minimumStock,
            'low_stock' => $stock <= $minimumStock,
            'pending_delivery_count' => (int) Sale::query()
                ->where('status', TransactionStatus::Active->value)
                ->where('delivery_status', '!=', DeliveryStatus::Delivered->value)
                ->count(),
        ];
    }

    /**
     * @return array<int, array{date: string, revenue: int}>
     */
    public function salesTrend(string $dateFrom, string $dateTo): array
    {
        return Sale::query()
            ->where('status', TransactionStatus::Active->value)
            ->whereDate('sale_date', '>=', $dateFrom)
            ->whereDate('sale_date', '<=', $dateTo)
            ->selectRaw('DATE(sale_date) as date')
            ->selectRaw('SUM(total_amount) as revenue')
            ->groupByRaw('DATE(sale_date)')
            ->orderByRaw('DATE(sale_date)')
            ->get()
            ->map(fn ($row) => [
                'date' => Carbon::parse($row->date)->toDateString(),
                'revenue' => (int) $row->revenue,
            ])
            ->all();
    }

    /**
     * @return array<int, array{date: string, gross_profit: float}>
     */
    public function profitTrend(string $dateFrom, string $dateTo): array
    {
        return $this->profit->dailySeries($dateFrom, $dateTo)
            ->map(fn (array $row) => [
                'date' => $row['date'],
                'gross_profit' => $row['gross_profit'],
            ])
            ->all();
    }

    /**
     * @return Collection<int, Sale>
     */
    private function activeSales(string $dateFrom, string $dateTo): Collection
    {
        return Sale::query()
            ->where('status', TransactionStatus::Active->value)
            ->whereDate('sale_date', '>=', $dateFrom)
            ->whereDate('sale_date', '<=', $dateTo)
            ->get(['total_amount', 'paid_amount']);
    }
}
