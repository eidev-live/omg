<?php

use App\Models\Customer;
use App\Models\SaleType;
use App\Models\User;
use App\Services\PurchaseService;
use App\Services\SaleService;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);

    app(PurchaseService::class)->create([
        'purchase_date' => now()->toDateString(),
        'quantity' => 1,
        'egg_per_unit' => 100,
        'total_cost' => 250000,
        'notes' => null,
    ]);

    $pack = SaleType::factory()->create(['name' => 'Pack', 'egg_quantity' => 10, 'selling_price' => 33000]);
    $customer = Customer::factory()->create();

    app(SaleService::class)->create([
        'sale_date' => now()->toDateString(),
        'customer_id' => $customer->id,
        'items' => [['sale_type_id' => $pack->id, 'quantity' => 2]],
        'paid_amount' => 30000,
        'payment_date' => null,
        'delivery_status' => 'PENDING',
        'notes' => null,
    ]);
});

test('guests cannot access reports', function () {
    auth()->logout();

    $this->get('/reports/sales')->assertRedirect('/login');
    $this->get('/reports/purchases')->assertRedirect('/login');
    $this->get('/reports/profit')->assertRedirect('/login');
    $this->get('/reports/stock')->assertRedirect('/login');
});

test('sales report renders summary and rows', function () {
    $this->get('/reports/sales')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('reports/Sales')
            ->where('summary.revenue', 66000)
            ->where('summary.paid', 30000)
            ->where('summary.outstanding', 36000)
            ->has('sales.data', 1));
});

test('purchases report renders summary', function () {
    $this->get('/reports/purchases')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('reports/Purchases')
            ->where('summary.total_cost', 250000)
            ->where('summary.egg_quantity', 100));
});

test('profit report renders totals', function () {
    $this->get('/reports/profit')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('reports/Profit')
            ->where('totals.revenue', 66000)
            ->where('totals.gross_profit', 16000)
            ->has('series'));
});

test('stock report renders summary', function () {
    $this->get('/reports/stock')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('reports/Stock')
            ->where('summary.current', 80)
            ->where('summary.stock_in', 100)
            ->where('summary.stock_out', 20));
});

test('sales report can be exported to csv', function () {
    $response = $this->get('/reports/sales/export');

    $response->assertOk();

    $content = $response->streamedContent();

    expect($content)->toContain('Invoice')
        ->and($content)->toContain('INV-');
});

test('profit report can be exported to csv', function () {
    $response = $this->get('/reports/profit/export');

    $response->assertOk();

    $content = $response->streamedContent();

    expect($content)->toContain('Laba Kotor')
        ->and($content)->toContain('Margin (%)');
});

test('csv export neutralises formula injection', function () {
    $customer = Customer::factory()->create(['name' => '=2+2']);
    $pack = SaleType::factory()->create(['name' => 'Pack', 'egg_quantity' => 10, 'selling_price' => 33000]);

    app(SaleService::class)->create([
        'sale_date' => now()->toDateString(),
        'customer_id' => $customer->id,
        'items' => [['sale_type_id' => $pack->id, 'quantity' => 1]],
        'paid_amount' => 0,
        'payment_date' => null,
        'delivery_status' => 'PENDING',
        'notes' => null,
    ]);

    $content = $this->get('/reports/sales/export')->streamedContent();

    expect($content)->toContain("'=2+2");
});
