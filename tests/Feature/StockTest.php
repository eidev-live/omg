<?php

use App\Models\User;
use App\Services\InventoryService;
use App\Services\PurchaseService;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->inventory = app(InventoryService::class);
    $this->purchases = app(PurchaseService::class);
});

function stockPurchase(int $eggs, int $cost): void
{
    app(PurchaseService::class)->create([
        'purchase_date' => now()->toDateString(),
        'quantity' => 1,
        'egg_per_unit' => $eggs,
        'total_cost' => $cost,
        'notes' => null,
    ]);
}

test('guests cannot access the stock page', function () {
    $this->get('/stock')->assertRedirect('/login');
});

test('stock page can be rendered', function () {
    $this->actingAs($this->user);

    stockPurchase(180, 475000);

    $this->actingAs($this->user)
        ->get('/stock')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('stock/Index')
            ->where('stock.current', 180)
            ->where('stock.consistent', true));
});

test('adjustment in increases stock', function () {
    $this->actingAs($this->user);

    stockPurchase(100, 250000);

    $this->actingAs($this->user)
        ->post('/stock/adjustments', [
            'type' => 'ADJUSTMENT_IN',
            'quantity' => 5,
            'reason' => 'Stok opname',
        ])
        ->assertRedirect();

    expect($this->inventory->currentStock())->toBe(105)
        ->and($this->inventory->isConsistent())->toBeTrue();
});

test('adjustment out decreases stock', function () {
    $this->actingAs($this->user);

    stockPurchase(100, 250000);

    $this->actingAs($this->user)
        ->post('/stock/adjustments', [
            'type' => 'ADJUSTMENT_OUT',
            'quantity' => 2,
            'reason' => 'Telur pecah',
        ]);

    expect($this->inventory->currentStock())->toBe(98);
});

test('adjustment out cannot make stock negative', function () {
    $this->actingAs($this->user);

    stockPurchase(10, 25000);

    $this->actingAs($this->user)
        ->post('/stock/adjustments', [
            'type' => 'ADJUSTMENT_OUT',
            'quantity' => 20,
            'reason' => 'Koreksi',
        ])
        ->assertSessionHasErrors('quantity');

    expect($this->inventory->currentStock())->toBe(10);
});

test('adjustment reason is required', function () {
    $this->actingAs($this->user);

    stockPurchase(10, 25000);

    $this->actingAs($this->user)
        ->post('/stock/adjustments', [
            'type' => 'ADJUSTMENT_IN',
            'quantity' => 1,
            'reason' => '',
        ])
        ->assertSessionHasErrors('reason');
});

test('adjustment type must be an adjustment type', function () {
    $this->actingAs($this->user);

    $this->actingAs($this->user)
        ->post('/stock/adjustments', [
            'type' => 'PURCHASE',
            'quantity' => 1,
            'reason' => 'Palsu',
        ])
        ->assertSessionHasErrors('type');
});
