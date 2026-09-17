<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePurchaseRequest;
use App\Models\Purchase;
use App\Services\PurchaseService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class PurchaseController extends Controller
{
    public function __construct(private readonly PurchaseService $purchases) {}

    public function index(Request $request): Response
    {
        $search = $request->string('search')->toString();
        $status = $request->string('status')->toString();

        $purchases = Purchase::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('purchase_number', 'like', "%{$search}%")
                        ->orWhere('notes', 'like', "%{$search}%");
                });
            })
            ->when($status !== '', function ($query) use ($status) {
                $query->where('status', $status === 'active' ? 'ACTIVE' : 'CANCELLED');
            })
            ->orderByDesc('purchase_date')
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString()
            ->through(fn (Purchase $purchase) => [
                'id' => $purchase->id,
                'purchase_number' => $purchase->purchase_number,
                'purchase_date' => $purchase->purchase_date?->toDateString(),
                'quantity' => $purchase->quantity,
                'egg_quantity' => $purchase->egg_quantity,
                'total_cost' => $purchase->total_cost,
                'cost_per_egg' => (float) $purchase->cost_per_egg,
                'status' => $purchase->status->value,
            ]);

        return Inertia::render('purchases/Index', [
            'purchases' => $purchases,
            'filters' => [
                'search' => $search,
                'status' => $status,
            ],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('purchases/Create');
    }

    public function store(StorePurchaseRequest $request): RedirectResponse
    {
        $purchase = $this->purchases->create($request->validated());

        return to_route('purchases.show', $purchase)->with('success', 'Pembelian berhasil disimpan.');
    }

    public function show(Purchase $purchase): Response
    {
        $purchase->load([
            'layer',
            'creator',
            'stockMovements' => fn ($query) => $query->orderByDesc('id'),
        ]);

        return Inertia::render('purchases/Show', [
            'purchase' => [
                'id' => $purchase->id,
                'purchase_number' => $purchase->purchase_number,
                'purchase_date' => $purchase->purchase_date?->toDateString(),
                'quantity' => $purchase->quantity,
                'egg_quantity' => $purchase->egg_quantity,
                'total_cost' => $purchase->total_cost,
                'cost_per_egg' => (float) $purchase->cost_per_egg,
                'notes' => $purchase->notes,
                'status' => $purchase->status->value,
                'cancelled_at' => $purchase->cancelled_at?->toIso8601String(),
                'cancel_reason' => $purchase->cancel_reason,
                'created_by' => $purchase->creator?->name,
                'layer' => $purchase->layer ? [
                    'quantity_received' => $purchase->layer->quantity_received,
                    'quantity_remaining' => $purchase->layer->quantity_remaining,
                    'unit_cost' => (float) $purchase->layer->unit_cost,
                ] : null,
                'movements' => $purchase->stockMovements->map(fn ($movement) => [
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

    public function cancel(Request $request, Purchase $purchase): RedirectResponse
    {
        $validated = $request->validate([
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        try {
            $this->purchases->cancel($purchase, $validated['reason'] ?? null);
        } catch (ValidationException $exception) {
            return back()->with('error', $exception->validator->errors()->first());
        }

        return back()->with('success', 'Pembelian berhasil dibatalkan dan stok dikoreksi.');
    }
}
