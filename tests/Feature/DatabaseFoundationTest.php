<?php

use App\Enums\TransactionStatus;
use App\Models\Customer;
use App\Models\InventoryLayer;
use App\Models\Purchase;
use App\Models\SaleType;
use App\Models\Setting;

test('customer is soft deleted instead of removed', function () {
    $customer = Customer::factory()->create();

    $customer->delete();

    expect(Customer::count())->toBe(0)
        ->and(Customer::withTrashed()->count())->toBe(1);
});

test('sale type stores egg quantity and price as integers', function () {
    $saleType = SaleType::factory()->create([
        'name' => 'Pack',
        'egg_quantity' => 10,
        'selling_price' => 33000,
    ]);

    $saleType->refresh();

    expect($saleType->egg_quantity)->toBe(10)
        ->and($saleType->selling_price)->toBe(33000)
        ->and($saleType->is_active)->toBeTrue();
});

test('settings can be stored and retrieved', function () {
    Setting::set(Setting::MINIMUM_STOCK, '30');
    expect(Setting::minimumStock())->toBe(30);

    Setting::set(Setting::MINIMUM_STOCK, '45');
    expect(Setting::minimumStock())->toBe(45);
});

test('purchase casts cost per egg with decimal precision', function () {
    $purchase = Purchase::factory()->create([
        'total_cost' => 475000,
        'egg_quantity' => 180,
        'cost_per_egg' => 475000 / 180,
    ]);

    expect((float) $purchase->refresh()->cost_per_egg)->toBe(2638.8889)
        ->and($purchase->status)->toBe(TransactionStatus::Active);
});

test('available inventory layers are ordered oldest first and exclude empty ones', function () {
    $oldest = InventoryLayer::factory()->create([
        'quantity_received' => 100,
        'quantity_remaining' => 40,
    ]);
    $newer = InventoryLayer::factory()->create([
        'quantity_received' => 100,
        'quantity_remaining' => 100,
    ]);
    InventoryLayer::factory()->create([
        'quantity_received' => 50,
        'quantity_remaining' => 0,
    ]);

    $layers = InventoryLayer::query()->available()->pluck('id')->all();

    expect($layers)->toBe([$oldest->id, $newer->id]);
});
