<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\StockMovement;
use App\Services\ReportService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function __construct(private readonly ReportService $reports) {}

    public function sales(Request $request): Response
    {
        $filters = $this->filters($request);

        $sales = $this->reports->salesQuery($filters)
            ->paginate(15)
            ->withQueryString()
            ->through(fn (Sale $sale) => [
                'id' => $sale->id,
                'invoice_number' => $sale->invoice_number,
                'sale_date' => $sale->sale_date?->toDateString(),
                'customer_name' => $sale->customer?->name,
                'total_amount' => $sale->total_amount,
                'paid_amount' => $sale->paid_amount,
                'outstanding' => $sale->outstanding(),
                'payment_status' => $sale->payment_status->value,
                'delivery_status' => $sale->delivery_status->value,
                'status' => $sale->status->value,
            ]);

        return Inertia::render('reports/Sales', [
            'sales' => $sales,
            'summary' => $this->reports->salesSummary($filters),
            'filters' => $filters,
            'customers' => Customer::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function purchases(Request $request): Response
    {
        $filters = $this->filters($request);

        $purchases = $this->reports->purchasesQuery($filters)
            ->paginate(15)
            ->withQueryString()
            ->through(fn (Purchase $purchase) => [
                'id' => $purchase->id,
                'purchase_number' => $purchase->purchase_number,
                'purchase_date' => $purchase->purchase_date?->toDateString(),
                'quantity' => $purchase->quantity,
                'egg_quantity' => $purchase->egg_quantity,
                'total_cost' => $purchase->total_cost,
                'cost_per_egg' => (float) $purchase->cost_per_egg,
            ]);

        return Inertia::render('reports/Purchases', [
            'purchases' => $purchases,
            'summary' => $this->reports->purchasesSummary($filters),
            'filters' => $filters,
        ]);
    }

    public function profit(Request $request): Response
    {
        $filters = $this->filters($request);
        $report = $this->reports->profitReport($filters);

        return Inertia::render('reports/Profit', [
            'totals' => $report['totals'],
            'series' => $report['series'],
            'filters' => $filters,
            'customers' => Customer::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function stock(Request $request): Response
    {
        $filters = $this->filters($request);

        $movements = $this->reports->stockMovementsQuery($filters)
            ->paginate(15)
            ->withQueryString()
            ->through(fn (StockMovement $movement) => [
                'id' => $movement->id,
                'movement_date' => $movement->movement_date?->toDateString(),
                'movement_type' => $movement->movement_type->value,
                'movement_label' => $movement->movement_type->label(),
                'quantity' => $movement->quantity,
                'notes' => $movement->notes,
            ]);

        return Inertia::render('reports/Stock', [
            'summary' => $this->reports->stockSummary($filters),
            'movements' => $movements,
            'filters' => $filters,
        ]);
    }

    public function salesExport(Request $request): StreamedResponse
    {
        $filters = $this->filters($request);

        $rows = $this->reports->salesQuery($filters)->get()->map(fn (Sale $sale) => [
            $sale->invoice_number,
            $sale->sale_date?->toDateString(),
            $sale->customer?->name,
            $sale->total_amount,
            $sale->paid_amount,
            $sale->outstanding(),
            $sale->payment_status->value,
            $sale->delivery_status->value,
            $sale->status->value,
        ]);

        return $this->csv('laporan-penjualan.csv', [
            'Invoice', 'Tanggal', 'Customer', 'Total', 'Dibayar', 'Sisa', 'Pembayaran', 'Pengiriman', 'Status',
        ], $rows);
    }

    public function purchasesExport(Request $request): StreamedResponse
    {
        $filters = $this->filters($request);

        $rows = $this->reports->purchasesQuery($filters)->get()->map(fn (Purchase $purchase) => [
            $purchase->purchase_number,
            $purchase->purchase_date?->toDateString(),
            $purchase->quantity,
            $purchase->egg_quantity,
            $purchase->total_cost,
            (float) $purchase->cost_per_egg,
        ]);

        return $this->csv('laporan-pembelian.csv', [
            'No. Pembelian', 'Tanggal', 'Jumlah Ikat', 'Total Telur', 'Total Biaya', 'HPP per Butir',
        ], $rows);
    }

    public function profitExport(Request $request): StreamedResponse
    {
        $filters = $this->filters($request);
        $series = $this->reports->profitReport($filters)['series'];

        $rows = collect($series)->map(fn (array $row) => [
            $row['date'],
            $row['revenue'],
            $row['cogs'],
            $row['gross_profit'],
            $row['revenue'] > 0 ? round($row['gross_profit'] / $row['revenue'] * 100, 2) : 0,
        ]);

        return $this->csv('laporan-profit.csv', [
            'Tanggal', 'Pendapatan', 'COGS', 'Laba Kotor', 'Margin (%)',
        ], $rows);
    }

    public function stockExport(Request $request): StreamedResponse
    {
        $filters = $this->filters($request);

        $rows = $this->reports->stockMovementsQuery($filters)->get()->map(fn (StockMovement $movement) => [
            $movement->movement_date?->toDateString(),
            $movement->movement_type->label(),
            $movement->quantity,
            $movement->notes,
        ]);

        return $this->csv('laporan-stock.csv', [
            'Tanggal', 'Jenis', 'Jumlah', 'Catatan',
        ], $rows);
    }

    /**
     * @return array<string, mixed>
     */
    private function filters(Request $request): array
    {
        return [
            'date_from' => $request->string('date_from')->toString() ?: null,
            'date_to' => $request->string('date_to')->toString() ?: null,
            'customer_id' => $request->integer('customer_id') ?: null,
            'payment' => $request->string('payment')->toString() ?: null,
            'delivery' => $request->string('delivery')->toString() ?: null,
        ];
    }

    /**
     * @param  array<int, string>  $headers
     * @param  iterable<int, array<int, mixed>>  $rows
     */
    private function csv(string $filename, array $headers, iterable $rows): StreamedResponse
    {
        return response()->streamDownload(function () use ($headers, $rows) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $headers);

            foreach ($rows as $row) {
                fputcsv($handle, array_map($this->escapeCsvValue(...), $row));
            }

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /**
     * Mencegah CSV formula injection di aplikasi spreadsheet.
     */
    private function escapeCsvValue(mixed $value): mixed
    {
        if (! is_string($value) || $value === '') {
            return $value;
        }

        if (in_array($value[0], ['=', '+', '-', '@', "\t", "\r"], true)) {
            return "'".$value;
        }

        return $value;
    }
}
