<?php

namespace App\Http\Controllers;

use App\Enums\DeliveryStatus;
use App\Http\Requests\StoreSaleRequest;
use App\Http\Requests\UpdateSaleDeliveryRequest;
use App\Http\Requests\UpdateSalePaymentRequest;
use App\Models\Customer;
use App\Models\Sale;
use App\Models\SaleType;
use App\Services\FifoCostingService;
use App\Services\SaleService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class SaleController extends Controller
{
    public function __construct(
        private readonly SaleService $sales,
        private readonly FifoCostingService $fifo,
    ) {}

    public function index(Request $request): Response
    {
        $search = $request->string('search')->toString();
        $payment = $request->string('payment')->toString();
        $delivery = $request->string('delivery')->toString();
        $dateFrom = $request->string('date_from')->toString();
        $dateTo = $request->string('date_to')->toString();

        $sales = Sale::query()
            ->with('customer')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('invoice_number', 'like', "%{$search}%")
                        ->orWhereHas('customer', fn ($query) => $query->where('name', 'like', "%{$search}%"));
                });
            })
            ->when($payment !== '', fn ($query) => $query->where('payment_status', $payment))
            ->when($delivery !== '', fn ($query) => $query->where('delivery_status', $delivery))
            ->when($dateFrom !== '', fn ($query) => $query->whereDate('sale_date', '>=', $dateFrom))
            ->when($dateTo !== '', fn ($query) => $query->whereDate('sale_date', '<=', $dateTo))
            ->orderByDesc('sale_date')
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString()
            ->through(fn (Sale $sale) => [
                'id' => $sale->id,
                'invoice_number' => $sale->invoice_number,
                'sale_date' => $sale->sale_date?->toDateString(),
                'customer_name' => $sale->customer?->name,
                'total_amount' => $sale->total_amount,
                'paid_amount' => $sale->paid_amount,
                'payment_status' => $sale->payment_status->value,
                'delivery_status' => $sale->delivery_status->value,
                'status' => $sale->status->value,
            ]);

        return Inertia::render('sales/Index', [
            'sales' => $sales,
            'filters' => [
                'search' => $search,
                'payment' => $payment,
                'delivery' => $delivery,
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
            ],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('sales/Create', [
            'customers' => Customer::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name']),
            'saleTypes' => SaleType::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name', 'egg_quantity', 'selling_price']),
            'stockAvailable' => $this->fifo->availableQuantity(),
            'today' => now()->toDateString(),
        ]);
    }

    public function store(StoreSaleRequest $request): RedirectResponse
    {
        $this->sales->create($request->validated());

        return to_route('sales.index')->with('success', 'Transaksi penjualan berhasil disimpan.');
    }

    public function show(Sale $sale): Response
    {
        $sale->load([
            'customer',
            'items.consumptions.layer',
            'creator',
            'stockMovements' => fn ($query) => $query->orderByDesc('id'),
        ]);

        $revenue = $sale->total_amount;
        $cogs = (float) $sale->items->sum(fn ($item) => (float) $item->total_cost);
        $profit = (float) $sale->items->sum(fn ($item) => (float) $item->profit);
        $totalEggs = (int) $sale->items->sum('egg_quantity');

        return Inertia::render('sales/Show', [
            'sale' => [
                'id' => $sale->id,
                'invoice_number' => $sale->invoice_number,
                'sale_date' => $sale->sale_date?->toDateString(),
                'customer' => $sale->customer?->name,
                'customer_phone' => $sale->customer?->phone,
                'total_amount' => $sale->total_amount,
                'paid_amount' => $sale->paid_amount,
                'outstanding' => $sale->outstanding(),
                'payment_status' => $sale->payment_status->value,
                'payment_date' => $sale->payment_date?->toDateString(),
                'delivery_status' => $sale->delivery_status->value,
                'delivered_at' => $sale->delivered_at?->toIso8601String(),
                'notes' => $sale->notes,
                'status' => $sale->status->value,
                'cancelled_at' => $sale->cancelled_at?->toIso8601String(),
                'cancel_reason' => $sale->cancel_reason,
                'created_by' => $sale->creator?->name,
                'total_eggs' => $totalEggs,
                'revenue' => $revenue,
                'cogs' => $cogs,
                'profit' => $profit,
                'margin' => $revenue > 0 ? round($profit / $revenue * 100, 1) : 0.0,
                'items' => $sale->items->map(fn ($item) => [
                    'id' => $item->id,
                    'sale_type_name' => $item->sale_type_name,
                    'quantity' => $item->quantity,
                    'egg_quantity' => $item->egg_quantity,
                    'unit_price' => $item->unit_price,
                    'subtotal' => $item->subtotal,
                    'unit_cost' => (float) $item->unit_cost,
                    'total_cost' => (float) $item->total_cost,
                    'profit' => (float) $item->profit,
                ]),
                'movements' => $sale->stockMovements->map(fn ($movement) => [
                    'id' => $movement->id,
                    'movement_date' => $movement->movement_date?->toDateString(),
                    'movement_type' => $movement->movement_type->value,
                    'movement_label' => $movement->movement_type->label(),
                    'quantity' => $movement->quantity,
                    'notes' => $movement->notes,
                ]),
            ],
        ]);
    }

    public function cancel(Request $request, Sale $sale): RedirectResponse
    {
        $validated = $request->validate([
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        try {
            $this->sales->cancel($sale, $validated['reason'] ?? null);
        } catch (ValidationException $exception) {
            return back()->with('error', $exception->validator->errors()->first());
        }

        return back()->with('success', 'Transaksi dibatalkan dan stok dikembalikan.');
    }

    public function updatePayment(UpdateSalePaymentRequest $request, Sale $sale): RedirectResponse
    {
        $validated = $request->validated();

        try {
            $this->sales->updatePayment($sale, (int) $validated['paid_amount'], $validated['payment_date'] ?? null);
        } catch (ValidationException $exception) {
            return back()->with('error', $exception->validator->errors()->first());
        }

        return back()->with('success', 'Pembayaran berhasil diperbarui.');
    }

    public function updateDelivery(UpdateSaleDeliveryRequest $request, Sale $sale): RedirectResponse
    {
        try {
            $this->sales->updateDelivery($sale, DeliveryStatus::from($request->validated()['delivery_status']));
        } catch (ValidationException $exception) {
            return back()->with('error', $exception->validator->errors()->first());
        }

        return back()->with('success', 'Status pengiriman berhasil diperbarui.');
    }
}
