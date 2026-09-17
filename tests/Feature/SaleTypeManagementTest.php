<?php

use App\Models\Customer;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SaleType;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
});

test('guests cannot access sale types', function () {
    $this->get('/sale-types')->assertRedirect('/login');
});

test('sale type can be created', function () {
    $this->actingAs($this->user)->post('/sale-types', [
        'name' => 'Pack',
        'egg_quantity' => 10,
        'selling_price' => 33000,
        'is_active' => true,
    ])->assertRedirect('/sale-types');

    $this->assertDatabaseHas('sale_types', [
        'name' => 'Pack',
        'egg_quantity' => 10,
        'selling_price' => 33000,
    ]);
});

test('sale type name must be unique', function () {
    SaleType::factory()->create(['name' => 'Pack']);

    $this->actingAs($this->user)
        ->post('/sale-types', ['name' => 'Pack', 'egg_quantity' => 10, 'selling_price' => 33000])
        ->assertSessionHasErrors('name');
});

test('sale type egg quantity must be at least one', function () {
    $this->actingAs($this->user)
        ->post('/sale-types', ['name' => 'Kosong', 'egg_quantity' => 0, 'selling_price' => 1000])
        ->assertSessionHasErrors('egg_quantity');
});

test('sale type can be updated', function () {
    $saleType = SaleType::factory()->create(['name' => 'Pack', 'selling_price' => 33000]);

    $this->actingAs($this->user)->put("/sale-types/{$saleType->id}", [
        'name' => 'Pack',
        'egg_quantity' => 10,
        'selling_price' => 35000,
        'is_active' => true,
    ])->assertRedirect('/sale-types');

    expect($saleType->refresh()->selling_price)->toBe(35000);
});

test('sale type active status can be toggled', function () {
    $saleType = SaleType::factory()->create(['is_active' => true]);

    $this->actingAs($this->user)->patch("/sale-types/{$saleType->id}/toggle");

    expect($saleType->refresh()->is_active)->toBeFalse();
});

test('unused sale type can be deleted', function () {
    $saleType = SaleType::factory()->create();

    $this->actingAs($this->user)
        ->delete("/sale-types/{$saleType->id}")
        ->assertRedirect('/sale-types');

    $this->assertDatabaseMissing('sale_types', ['id' => $saleType->id]);
});

test('sale type used by a transaction cannot be deleted', function () {
    $saleType = SaleType::factory()->create(['name' => 'Pack', 'egg_quantity' => 10, 'selling_price' => 33000]);
    $customer = Customer::factory()->create();

    $sale = Sale::query()->create([
        'invoice_number' => 'INV-20260916-0001',
        'sale_date' => now()->toDateString(),
        'customer_id' => $customer->id,
        'total_amount' => 33000,
    ]);

    SaleItem::query()->create([
        'sale_id' => $sale->id,
        'sale_type_id' => $saleType->id,
        'sale_type_name' => $saleType->name,
        'quantity' => 1,
        'egg_quantity' => 10,
        'unit_price' => 33000,
        'subtotal' => 33000,
        'unit_cost' => 2500,
        'total_cost' => 25000,
        'profit' => 8000,
    ]);

    $this->actingAs($this->user)->delete("/sale-types/{$saleType->id}");

    $this->assertDatabaseHas('sale_types', ['id' => $saleType->id]);
});
