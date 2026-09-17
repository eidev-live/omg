<?php

namespace App\Services;

use App\Enums\TransactionStatus;
use App\Models\SaleItem;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class ProfitService
{
    /**
     * Ringkasan laba dari transaksi aktual (transaksi batal tidak dihitung).
     *
     * @return array{revenue: int, cogs: float, gross_profit: float, margin: float, sale_count: int}
     */
    public function summary(?string $dateFrom = null, ?string $dateTo = null, ?int $customerId = null): array
    {
        $query = $this->query($dateFrom, $dateTo, $customerId);

        $revenue = (int) $query->sum('sale_items.subtotal');
        $cogs = (float) $query->sum('sale_items.total_cost');
        $grossProfit = $revenue - $cogs;

        return [
            'revenue' => $revenue,
            'cogs' => $cogs,
            'gross_profit' => $grossProfit,
            'margin' => $revenue > 0 ? round($grossProfit / $revenue * 100, 2) : 0.0,
            'sale_count' => (int) $query->distinct()->count('sales.id'),
        ];
    }

    /**
     * Ringkasan laba harian, untuk grafik tren.
     *
     * @return Collection<int, array{date: string, revenue: int, cogs: float, gross_profit: float}>
     */
    public function dailySeries(?string $dateFrom = null, ?string $dateTo = null, ?int $customerId = null): Collection
    {
        return $this->query($dateFrom, $dateTo, $customerId)
            ->selectRaw('sales.sale_date as date')
            ->selectRaw('SUM(sale_items.subtotal) as revenue')
            ->selectRaw('SUM(sale_items.total_cost) as cogs')
            ->groupBy('sales.sale_date')
            ->orderBy('sales.sale_date')
            ->get()
            ->map(fn ($row) => [
                'date' => Carbon::parse($row->date)->toDateString(),
                'revenue' => (int) $row->revenue,
                'cogs' => (float) $row->cogs,
                'gross_profit' => (float) ($row->revenue - $row->cogs),
            ]);
    }

    private function query(?string $dateFrom, ?string $dateTo, ?int $customerId): Builder
    {
        return SaleItem::query()
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->where('sales.status', TransactionStatus::Active->value)
            ->when($dateFrom !== null, fn ($query) => $query->whereDate('sales.sale_date', '>=', $dateFrom))
            ->when($dateTo !== null, fn ($query) => $query->whereDate('sales.sale_date', '<=', $dateTo))
            ->when($customerId !== null, fn ($query) => $query->where('sales.customer_id', $customerId));
    }
}
