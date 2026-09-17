<?php

namespace App\Http\Controllers;

use App\Enums\StockMovementType;
use App\Http\Requests\StoreStockAdjustmentRequest;
use App\Models\InventoryLayer;
use App\Models\Setting;
use App\Models\StockMovement;
use App\Services\InventoryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class StockController extends Controller
{
    public function __construct(private readonly InventoryService $inventory) {}

    public function index(Request $request): Response
    {
        $layers = InventoryLayer::query()
            ->with('purchase:id,purchase_number,purchase_date')
            ->orderBy('created_at')
            ->orderBy('id')
            ->paginate(15, ['*'], 'layers_page')
            ->withQueryString()
            ->through(fn (InventoryLayer $layer) => [
                'id' => $layer->id,
                'purchase_number' => $layer->purchase?->purchase_number,
                'purchase_date' => $layer->purchase?->purchase_date?->toDateString(),
                'quantity_received' => $layer->quantity_received,
                'quantity_remaining' => $layer->quantity_remaining,
                'unit_cost' => (float) $layer->unit_cost,
            ]);

        $movements = StockMovement::query()
            ->orderByDesc('movement_date')
            ->orderByDesc('id')
            ->paginate(15, ['*'], 'movements_page')
            ->withQueryString()
            ->through(fn (StockMovement $movement) => [
                'id' => $movement->id,
                'movement_date' => $movement->movement_date?->toDateString(),
                'movement_type' => $movement->movement_type->value,
                'movement_label' => $movement->movement_type->label(),
                'quantity' => $movement->quantity,
                'notes' => $movement->notes,
            ]);

        return Inertia::render('stock/Index', [
            'stock' => [
                'current' => $this->inventory->currentStock(),
                'layers_remaining' => $this->inventory->layersRemaining(),
                'consistent' => $this->inventory->isConsistent(),
                'inventory_value' => $this->inventory->inventoryValue(),
                'minimum_stock' => Setting::minimumStock(),
            ],
            'layers' => $layers,
            'movements' => $movements,
        ]);
    }

    public function adjust(StoreStockAdjustmentRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $this->inventory->adjust(
            StockMovementType::from($validated['type']),
            (int) $validated['quantity'],
            $validated['reason'],
        );

        return back()->with('success', 'Penyesuaian stok berhasil disimpan.');
    }
}
