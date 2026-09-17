<?php

use App\Exceptions\InsufficientStockException;
use App\Models\InventoryLayer;
use App\Services\FifoCostingService;

beforeEach(function () {
    $this->fifo = app(FifoCostingService::class);
});

function fifoLayer(int $received, float $unitCost, ?int $remaining = null): InventoryLayer
{
    return InventoryLayer::factory()->create([
        'quantity_received' => $received,
        'quantity_remaining' => $remaining ?? $received,
        'unit_cost' => $unitCost,
    ]);
}

test('available quantity sums remaining layers only', function () {
    fifoLayer(100, 2500);
    fifoLayer(100, 2800, 80);
    fifoLayer(50, 3000, 0);

    expect($this->fifo->availableQuantity())->toBe(180);
});

test('can fulfil reflects available stock', function () {
    fifoLayer(50, 2500);

    expect($this->fifo->canFulfill(50))->toBeTrue()
        ->and($this->fifo->canFulfill(51))->toBeFalse();
});

test('fifo consumes the oldest layer first with correct cogs', function () {
    $old = fifoLayer(50, 2500);
    $new = fifoLayer(100, 2800);

    $result = $this->fifo->consume(70);

    expect($result['total_quantity'])->toBe(70)
        ->and($result['total_cost'])->toBe(181000.0)
        ->and($result['consumptions'])->toHaveCount(2)
        ->and($result['consumptions'][0])->toMatchArray([
            'inventory_layer_id' => $old->id,
            'quantity' => 50,
            'unit_cost' => 2500.0,
            'total_cost' => 125000.0,
        ])
        ->and($result['consumptions'][1])->toMatchArray([
            'inventory_layer_id' => $new->id,
            'quantity' => 20,
            'unit_cost' => 2800.0,
            'total_cost' => 56000.0,
        ]);

    expect($old->refresh()->quantity_remaining)->toBe(0)
        ->and($new->refresh()->quantity_remaining)->toBe(80);
});

test('fifo stops exactly at a layer boundary', function () {
    $first = fifoLayer(100, 2500);
    $second = fifoLayer(100, 2800);

    $result = $this->fifo->consume(100);

    expect($result['consumptions'])->toHaveCount(1)
        ->and($result['total_cost'])->toBe(250000.0)
        ->and($first->refresh()->quantity_remaining)->toBe(0)
        ->and($second->refresh()->quantity_remaining)->toBe(100);
});

test('fifo consumes across several layers', function () {
    $a = fifoLayer(30, 1000);
    $b = fifoLayer(30, 2000);
    $c = fifoLayer(30, 3000);

    $result = $this->fifo->consume(65);

    expect($result['consumptions'])->toHaveCount(3)
        ->and($result['total_cost'])->toBe(105000.0)
        ->and($a->refresh()->quantity_remaining)->toBe(0)
        ->and($b->refresh()->quantity_remaining)->toBe(0)
        ->and($c->refresh()->quantity_remaining)->toBe(25);
});

test('empty layers are skipped', function () {
    fifoLayer(50, 2500, 0);
    $available = fifoLayer(30, 2800);

    $result = $this->fifo->consume(30);

    expect($result['consumptions'])->toHaveCount(1)
        ->and($result['consumptions'][0]['inventory_layer_id'])->toBe($available->id);
});

test('insufficient stock throws and leaves layers untouched', function () {
    $layer = fifoLayer(20, 2500);

    expect(fn () => $this->fifo->consume(30))->toThrow(InsufficientStockException::class);

    expect($layer->refresh()->quantity_remaining)->toBe(20);
});

test('insufficient stock exception reports the amounts', function () {
    fifoLayer(20, 2500);

    try {
        $this->fifo->consume(30);
        $this->fail('InsufficientStockException was not thrown.');
    } catch (InsufficientStockException $exception) {
        expect($exception->available)->toBe(20)
            ->and($exception->required)->toBe(30);
    }
});

test('consume rejects a non positive quantity', function () {
    expect(fn () => $this->fifo->consume(0))->toThrow(InvalidArgumentException::class);
});

test('restore returns quantities to their original layers', function () {
    $a = fifoLayer(50, 2500);
    $b = fifoLayer(100, 2800);

    $result = $this->fifo->consume(70);
    $this->fifo->restore($result['consumptions']);

    expect($a->refresh()->quantity_remaining)->toBe(50)
        ->and($b->refresh()->quantity_remaining)->toBe(100);
});
