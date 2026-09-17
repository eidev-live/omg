<?php

use App\Models\Customer;
use App\Models\Sale;
use App\Models\SaleType;
use App\Models\User;
use App\Services\ProfitService;
use App\Services\PurchaseService;
use App\Services\SaleService;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);

    $this->profit = app(ProfitService::class);

    app(PurchaseService::class)->create([
        'purchase_date' => now()->toDateString(),
        'quantity' => 1,
        'egg_per_unit' => 200,
        'total_cost' => 500000,
        'notes' => null,
    ]);

    $this->pack = SaleType::factory()->create(['name' => 'Pack', 'egg_quantity' => 10, 'selling_price' => 33000]);
    $this->customer = Customer::factory()->create();
});

function profitSale(SaleType $pack, Customer $customer, int $quantity, string $date): Sale
{
    return app(SaleService::class)->create([
        'sale_date' => $date,
        'customer_id' => $customer->id,
        'items' => [['sale_type_id' => $pack->id, 'quantity' => $quantity]],
        'paid_amount' => 0,
        'payment_date' => null,
        'delivery_status' => 'PENDING',
        'notes' => null,
    ]);
}

test('summary computes revenue cogs profit and margin', function () {
    profitSale($this->pack, $this->customer, 1, now()->toDateString());

    $summary = $this->profit->summary();

    expect($summary['revenue'])->toBe(33000)
        ->and($summary['cogs'])->toBe(25000.0)
        ->and($summary['gross_profit'])->toBe(8000.0)
        ->and($summary['margin'])->toBe(24.24)
        ->and($summary['sale_count'])->toBe(1);
});

test('summary aggregates multiple sales', function () {
    profitSale($this->pack, $this->customer, 1, now()->toDateString());
    profitSale($this->pack, $this->customer, 1, now()->toDateString());

    $summary = $this->profit->summary();

    expect($summary['revenue'])->toBe(66000)
        ->and($summary['cogs'])->toBe(50000.0)
        ->and($summary['gross_profit'])->toBe(16000.0);
});

test('margin is zero when there is no revenue', function () {
    $summary = $this->profit->summary();

    expect($summary['revenue'])->toBe(0)
        ->and($summary['gross_profit'])->toBe(0.0)
        ->and($summary['margin'])->toBe(0.0);
});

test('cancelled sales are excluded from profit', function () {
    $sale = profitSale($this->pack, $this->customer, 1, now()->toDateString());

    app(SaleService::class)->cancel($sale);

    $summary = $this->profit->summary();

    expect($summary['revenue'])->toBe(0)
        ->and($summary['cogs'])->toBe(0.0)
        ->and($summary['margin'])->toBe(0.0);
});

test('summary can be filtered by date range', function () {
    profitSale($this->pack, $this->customer, 1, '2026-01-15');
    profitSale($this->pack, $this->customer, 1, '2026-06-15');

    $summary = $this->profit->summary('2026-06-01', '2026-06-30');

    expect($summary['revenue'])->toBe(33000)
        ->and($summary['sale_count'])->toBe(1);
});

test('summary can be filtered by customer', function () {
    $other = Customer::factory()->create();

    profitSale($this->pack, $this->customer, 1, now()->toDateString());
    profitSale($this->pack, $other, 2, now()->toDateString());

    $summary = $this->profit->summary(customerId: $this->customer->id);

    expect($summary['revenue'])->toBe(33000)
        ->and($summary['sale_count'])->toBe(1);
});

test('daily series groups profit per day', function () {
    profitSale($this->pack, $this->customer, 1, '2026-06-01');
    profitSale($this->pack, $this->customer, 1, '2026-06-01');
    profitSale($this->pack, $this->customer, 1, '2026-06-02');

    $series = $this->profit->dailySeries('2026-06-01', '2026-06-30');

    expect($series)->toHaveCount(2)
        ->and($series[0]['date'])->toBe('2026-06-01')
        ->and($series[0]['revenue'])->toBe(66000)
        ->and($series[0]['gross_profit'])->toBe(16000.0)
        ->and($series[1]['revenue'])->toBe(33000);
});
