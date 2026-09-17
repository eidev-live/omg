<?php

use App\Enums\StockMovementType;
use App\Enums\TransactionStatus;
use App\Models\StockMovement;
use App\Models\User;
use App\Services\InventoryService;
use App\Services\PurchaseService;
use Illuminate\Validation\ValidationException;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->service = app(PurchaseService::class);
    $this->inventory = app(InventoryService::class);
});

function purchaseData(array $overrides = []): array
{
    return array_merge([
        'purchase_date' => now()->toDateString(),
        'quantity' => 1,
        'egg_per_unit' => 180,
        'total_cost' => 475000,
        'notes' => null,
    ], $overrides);
}

test('guests cannot access purchases', function () {
    $this->get('/purchases')->assertRedirect('/login');
});

test('creating a purchase increases stock and creates layer and movement', function () {
    $this->actingAs($this->user);

    $purchase = $this->service->create(purchaseData());

    expect($purchase->egg_quantity)->toBe(180)
        ->and((float) $purchase->cost_per_egg)->toBe(2638.8889)
        ->and($purchase->purchase_number)->toStartWith('PUR-')
        ->and($purchase->layer->quantity_received)->toBe(180)
        ->and($purchase->layer->quantity_remaining)->toBe(180);

    expect($this->inventory->currentStock())->toBe(180)
        ->and($this->inventory->layersRemaining())->toBe(180)
        ->and($this->inventory->isConsistent())->toBeTrue();

    $movement = StockMovement::query()->first();

    expect($movement->movement_type)->toBe(StockMovementType::Purchase)
        ->and($movement->quantity)->toBe(180);
});

test('purchase number is sequential per day', function () {
    $this->actingAs($this->user);

    $first = $this->service->create(purchaseData());
    $second = $this->service->create(purchaseData());

    expect($second->purchase_number)->not->toBe($first->purchase_number)
        ->and((int) substr($second->purchase_number, -4))->toBe((int) substr($first->purchase_number, -4) + 1);
});

test('a purchase can be created through the endpoint', function () {
    $this->actingAs($this->user)
        ->post('/purchases', purchaseData(['quantity' => 2, 'egg_per_unit' => 30, 'total_cost' => 182000]))
        ->assertRedirect();

    $this->assertDatabaseHas('purchases', ['egg_quantity' => 60, 'total_cost' => 182000]);

    expect($this->inventory->currentStock())->toBe(60);
});

test('cancelling an unused purchase reverses stock', function () {
    $this->actingAs($this->user);

    $purchase = $this->service->create(purchaseData());

    $this->service->cancel($purchase, 'Salah input');

    expect($purchase->refresh()->status)->toBe(TransactionStatus::Cancelled)
        ->and($purchase->layer->refresh()->quantity_remaining)->toBe(0);

    expect($this->inventory->currentStock())->toBe(0)
        ->and($this->inventory->layersRemaining())->toBe(0)
        ->and($this->inventory->isConsistent())->toBeTrue();

    expect(StockMovement::query()->where('movement_type', StockMovementType::PurchaseCancel->value)->exists())->toBeTrue();
});

test('a purchase that has been consumed cannot be cancelled', function () {
    $this->actingAs($this->user);

    $purchase = $this->service->create(purchaseData());
    $purchase->layer->update(['quantity_remaining' => 100]);

    expect(fn () => $this->service->cancel($purchase))->toThrow(ValidationException::class);

    expect($purchase->refresh()->status)->toBe(TransactionStatus::Active);
});
