<?php

use App\Enums\TransactionStatus;
use App\Models\Customer;
use App\Models\Sale;
use App\Models\SaleItemConsumption;
use App\Models\SaleType;
use App\Models\StockMovement;
use App\Models\User;
use App\Services\InventoryService;
use App\Services\PurchaseService;
use App\Services\SaleService;
use Illuminate\Validation\ValidationException;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);

    $this->sales = app(SaleService::class);
    $this->inventory = app(InventoryService::class);
    $this->customer = Customer::factory()->create();
});

function seedStock(int $eggs, int $cost): void
{
    app(PurchaseService::class)->create([
        'purchase_date' => now()->toDateString(),
        'quantity' => 1,
        'egg_per_unit' => $eggs,
        'total_cost' => $cost,
        'notes' => null,
    ]);
}

function makeSale(array $overrides = []): Sale
{
    return app(SaleService::class)->create(array_merge([
        'sale_date' => now()->toDateString(),
        'customer_id' => test()->customer->id,
        'items' => [],
        'paid_amount' => 0,
        'payment_date' => null,
        'delivery_status' => 'PENDING',
        'notes' => null,
    ], $overrides));
}

test('guests cannot access sales', function () {
    auth()->logout();

    $this->get('/sales')->assertRedirect('/login');
});

test('a multi item sale consumes stock with fifo cogs', function () {
    seedStock(100, 250000);
    seedStock(100, 280000);

    $pack = SaleType::factory()->create(['name' => 'Pack', 'egg_quantity' => 10, 'selling_price' => 33000]);
    $tray = SaleType::factory()->create(['name' => 'Tray', 'egg_quantity' => 30, 'selling_price' => 91000]);

    $sale = $this->sales->create([
        'sale_date' => now()->toDateString(),
        'customer_id' => $this->customer->id,
        'items' => [
            ['sale_type_id' => $pack->id, 'quantity' => 2],
            ['sale_type_id' => $tray->id, 'quantity' => 1],
        ],
        'paid_amount' => 100000,
        'payment_date' => null,
        'delivery_status' => 'PENDING',
        'notes' => null,
    ]);

    $sale->load('items');

    expect($sale->invoice_number)->toStartWith('INV-')
        ->and($sale->total_amount)->toBe(157000)
        ->and($sale->items)->toHaveCount(2)
        ->and((float) $sale->items[0]->total_cost)->toBe(50000.0)
        ->and($sale->items[0]->subtotal)->toBe(66000)
        ->and((float) $sale->items[1]->total_cost)->toBe(75000.0)
        ->and($sale->items[1]->subtotal)->toBe(91000);

    expect($this->inventory->currentStock())->toBe(150)
        ->and($this->inventory->layersRemaining())->toBe(150)
        ->and($this->inventory->isConsistent())->toBeTrue();

    expect(StockMovement::query()->where('movement_type', 'SALE')->value('quantity'))->toBe(-50)
        ->and(SaleItemConsumption::query()->count())->toBe(2);
});

test('sales are rejected when stock is insufficient', function () {
    seedStock(20, 50000);

    $pack = SaleType::factory()->create(['name' => 'Pack', 'egg_quantity' => 10, 'selling_price' => 33000]);

    expect(fn () => makeSale(['items' => [['sale_type_id' => $pack->id, 'quantity' => 3]]]))
        ->toThrow(ValidationException::class);

    expect($this->inventory->currentStock())->toBe(20)
        ->and(Sale::query()->count())->toBe(0);
});

test('sale is rejected through the endpoint when stock is insufficient', function () {
    seedStock(20, 50000);

    $pack = SaleType::factory()->create(['name' => 'Pack', 'egg_quantity' => 10, 'selling_price' => 33000]);

    $this->post('/sales', [
        'sale_date' => now()->toDateString(),
        'customer_id' => $this->customer->id,
        'items' => [['sale_type_id' => $pack->id, 'quantity' => 3]],
        'delivery_status' => 'PENDING',
    ])->assertSessionHasErrors('items');

    expect($this->inventory->currentStock())->toBe(20);
});

test('paid amount cannot exceed the transaction total', function () {
    seedStock(50, 100000);

    $pack = SaleType::factory()->create(['name' => 'Pack', 'egg_quantity' => 10, 'selling_price' => 33000]);

    expect(fn () => makeSale([
        'items' => [['sale_type_id' => $pack->id, 'quantity' => 1]],
        'paid_amount' => 50000,
    ]))->toThrow(ValidationException::class);

    expect(Sale::query()->count())->toBe(0);
});

test('payment status is computed from the paid amount', function () {
    seedStock(50, 100000);

    $pack = SaleType::factory()->create(['name' => 'Pack', 'egg_quantity' => 10, 'selling_price' => 33000]);

    $unpaid = makeSale(['items' => [['sale_type_id' => $pack->id, 'quantity' => 1]], 'paid_amount' => 0]);
    $partial = makeSale(['items' => [['sale_type_id' => $pack->id, 'quantity' => 1]], 'paid_amount' => 10000]);
    $paid = makeSale(['items' => [['sale_type_id' => $pack->id, 'quantity' => 1]], 'paid_amount' => 33000]);

    expect($unpaid->payment_status->value)->toBe('UNPAID')
        ->and($unpaid->outstanding())->toBe(33000)
        ->and($partial->payment_status->value)->toBe('PARTIAL')
        ->and($partial->outstanding())->toBe(23000)
        ->and($paid->payment_status->value)->toBe('PAID')
        ->and($paid->outstanding())->toBe(0);
});

test('fifo cogs is not affected by later price changes of the sale type', function () {
    seedStock(50, 125000);

    $pack = SaleType::factory()->create(['name' => 'Pack', 'egg_quantity' => 10, 'selling_price' => 33000]);

    $sale = makeSale(['items' => [['sale_type_id' => $pack->id, 'quantity' => 2]]]);

    $pack->update(['selling_price' => 35000]);

    $item = $sale->items()->first();

    expect($item->unit_price)->toBe(33000)
        ->and($item->subtotal)->toBe(66000);
});

test('an inactive sale type cannot be used', function () {
    seedStock(50, 125000);

    $pack = SaleType::factory()->create(['name' => 'Pack', 'egg_quantity' => 10, 'selling_price' => 33000, 'is_active' => false]);

    expect(fn () => makeSale(['items' => [['sale_type_id' => $pack->id, 'quantity' => 1]]]))
        ->toThrow(ValidationException::class);
});

test('cancelling a sale restores stock and layers', function () {
    seedStock(100, 250000);

    $pack = SaleType::factory()->create(['name' => 'Pack', 'egg_quantity' => 10, 'selling_price' => 33000]);

    $sale = makeSale(['items' => [['sale_type_id' => $pack->id, 'quantity' => 2]]]);

    expect($this->inventory->currentStock())->toBe(80);

    $this->sales->cancel($sale, 'Customer batal');

    expect($sale->refresh()->status)->toBe(TransactionStatus::Cancelled)
        ->and($this->inventory->currentStock())->toBe(100)
        ->and($this->inventory->layersRemaining())->toBe(100)
        ->and($this->inventory->isConsistent())->toBeTrue();

    expect(StockMovement::query()->where('movement_type', 'SALE_CANCEL')->value('quantity'))->toBe(20);
});

test('a cancelled sale cannot be cancelled twice', function () {
    seedStock(50, 125000);

    $pack = SaleType::factory()->create(['name' => 'Pack', 'egg_quantity' => 10, 'selling_price' => 33000]);
    $sale = makeSale(['items' => [['sale_type_id' => $pack->id, 'quantity' => 1]]]);

    $this->sales->cancel($sale);

    expect(fn () => $this->sales->cancel($sale))->toThrow(ValidationException::class);
});

test('a sale can be created through the endpoint', function () {
    seedStock(50, 125000);

    $pack = SaleType::factory()->create(['name' => 'Pack', 'egg_quantity' => 10, 'selling_price' => 33000]);

    $this->post('/sales', [
        'sale_date' => now()->toDateString(),
        'customer_id' => $this->customer->id,
        'items' => [['sale_type_id' => $pack->id, 'quantity' => 2]],
        'paid_amount' => 66000,
        'delivery_status' => 'PENDING',
    ])->assertRedirect();

    $this->assertDatabaseCount('sales', 1);
    $this->assertDatabaseCount('sale_items', 1);

    expect($this->inventory->currentStock())->toBe(30);
});
