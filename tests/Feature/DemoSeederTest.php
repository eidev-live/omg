<?php

use App\Models\Customer;
use App\Models\InventoryLayer;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\SaleType;
use App\Services\InventoryService;

test('demo seeder creates consistent demo data', function () {
    $this->seed();

    expect(Customer::query()->count())->toBe(3)
        ->and(SaleType::query()->whereIn('name', ['Pack', 'Tray', 'Ikat'])->count())->toBe(3)
        ->and(Purchase::query()->count())->toBe(2)
        ->and(Sale::query()->count())->toBe(5);

    $inventory = app(InventoryService::class);

    expect($inventory->isConsistent())->toBeTrue()
        ->and($inventory->currentStock())->toBe(260);

    // FIFO sudah melintasi lebih dari satu layer.
    expect(InventoryLayer::query()->where('quantity_remaining', '>', 0)->count())->toBe(1);
});

test('demo seeder demonstrates every payment and delivery status', function () {
    $this->seed();

    $payments = Sale::query()->get()->map(fn (Sale $sale) => $sale->payment_status->value);

    expect($payments)->toContain('PAID')
        ->toContain('PARTIAL')
        ->toContain('UNPAID');

    $deliveries = Sale::query()->get()->map(fn (Sale $sale) => $sale->delivery_status->value);

    expect($deliveries)->toContain('DELIVERED')
        ->toContain('SHIPPED')
        ->toContain('PENDING');
});
