<?php

use App\Models\Customer;
use App\Models\SaleType;
use App\Models\Setting;
use App\Models\User;
use App\Services\DashboardService;
use App\Services\PurchaseService;
use App\Services\SaleService;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);

    app(PurchaseService::class)->create([
        'purchase_date' => now()->toDateString(),
        'quantity' => 1,
        'egg_per_unit' => 200,
        'total_cost' => 500000,
        'notes' => null,
    ]);

    $this->pack = SaleType::factory()->create(['name' => 'Pack', 'egg_quantity' => 10, 'selling_price' => 33000]);
    $this->customer = Customer::factory()->create();

    $this->saleOne = app(SaleService::class)->create([
        'sale_date' => now()->toDateString(),
        'customer_id' => $this->customer->id,
        'items' => [['sale_type_id' => $this->pack->id, 'quantity' => 1]],
        'paid_amount' => 10000,
        'payment_date' => null,
        'delivery_status' => 'PENDING',
        'notes' => null,
    ]);

    $this->saleTwo = app(SaleService::class)->create([
        'sale_date' => now()->toDateString(),
        'customer_id' => $this->customer->id,
        'items' => [['sale_type_id' => $this->pack->id, 'quantity' => 1]],
        'paid_amount' => 0,
        'payment_date' => null,
        'delivery_status' => 'PENDING',
        'notes' => null,
    ]);

    $this->from = now()->copy()->startOfMonth()->toDateString();
    $this->to = now()->copy()->endOfMonth()->toDateString();
});

test('dashboard summary reflects actual transactions', function () {
    $summary = app(DashboardService::class)->summary($this->from, $this->to);

    expect($summary['revenue'])->toBe(66000)
        ->and($summary['sales_count'])->toBe(2)
        ->and($summary['cash_in'])->toBe(10000)
        ->and($summary['outstanding'])->toBe(56000)
        ->and($summary['outstanding_count'])->toBe(2)
        ->and($summary['purchases_total'])->toBe(500000)
        ->and($summary['purchases_count'])->toBe(1)
        ->and($summary['cogs'])->toBe(50000.0)
        ->and($summary['gross_profit'])->toBe(16000.0)
        ->and($summary['stock'])->toBe(180)
        ->and($summary['pending_delivery_count'])->toBe(2)
        ->and($summary['low_stock'])->toBeFalse();
});

test('dashboard summary can be filtered by period', function () {
    $lastMonth = now()->copy()->subMonthNoOverflow()->startOfMonth()->toDateString();
    $lastMonthEnd = now()->copy()->subMonthNoOverflow()->endOfMonth()->toDateString();

    $summary = app(DashboardService::class)->summary($lastMonth, $lastMonthEnd);

    expect($summary['revenue'])->toBe(0)
        ->and($summary['sales_count'])->toBe(0);
});

test('dashboard summary flags low stock', function () {
    app(PurchaseService::class)->create([
        'purchase_date' => now()->toDateString(),
        'quantity' => 1,
        'egg_per_unit' => 1,
        'total_cost' => 1000,
        'notes' => null,
    ]);

    Setting::set(Setting::MINIMUM_STOCK, '9999');

    $summary = app(DashboardService::class)->summary($this->from, $this->to);

    expect($summary['low_stock'])->toBeTrue();
});

test('sales trend groups revenue per day', function () {
    $trend = app(DashboardService::class)->salesTrend($this->from, $this->to);

    expect($trend)->toHaveCount(1)
        ->and($trend[0]['revenue'])->toBe(66000);
});

test('dashboard page renders with metrics', function () {
    $this->get('/dashboard')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Dashboard')
            ->where('summary.revenue', 66000)
            ->where('summary.stock', 180)
            ->has('salesTrend', 1)
            ->has('profitTrend', 1));
});
