<?php

use App\Models\Customer;
use App\Models\User;
use Inertia\Testing\AssertableInertia;

beforeEach(function () {
    $this->user = User::factory()->create();
});

test('guests cannot access the customer page', function () {
    $this->get('/customers')->assertRedirect('/login');
});

test('customer index can be rendered', function () {
    Customer::factory()->count(3)->create();

    $this->actingAs($this->user)
        ->get('/customers')
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('customers/Index')
            ->has('customers.data', 3));
});

test('customer index can be searched and filtered by status', function () {
    Customer::factory()->create(['name' => 'Budi Santoso']);
    Customer::factory()->create(['name' => 'Sari', 'is_active' => false]);

    $this->actingAs($this->user)
        ->get('/customers?search=Budi')
        ->assertInertia(fn (AssertableInertia $page) => $page->has('customers.data', 1));

    $this->actingAs($this->user)
        ->get('/customers?status=inactive')
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->has('customers.data', 1)
            ->where('customers.data.0.name', 'Sari'));
});

test('customer can be created', function () {
    $this->actingAs($this->user)->post('/customers', [
        'name' => 'Andi Wijaya',
        'phone' => '081234567890',
        'location' => 'Bandung',
        'notes' => 'Langganan mingguan',
        'is_active' => true,
    ])->assertRedirect('/customers');

    $this->assertDatabaseHas('customers', [
        'name' => 'Andi Wijaya',
        'phone' => '081234567890',
        'is_active' => true,
    ]);
});

test('customer name is required', function () {
    $this->actingAs($this->user)
        ->post('/customers', ['name' => ''])
        ->assertSessionHasErrors('name');
});

test('customer can be updated', function () {
    $customer = Customer::factory()->create(['name' => 'Lama']);

    $this->actingAs($this->user)->put("/customers/{$customer->id}", [
        'name' => 'Baru',
        'is_active' => true,
    ])->assertRedirect('/customers');

    expect($customer->refresh()->name)->toBe('Baru');
});

test('customer active status can be toggled', function () {
    $customer = Customer::factory()->create(['is_active' => true]);

    $this->actingAs($this->user)->patch("/customers/{$customer->id}/toggle");

    expect($customer->refresh()->is_active)->toBeFalse();
});

test('customer is soft deleted and history is preserved', function () {
    $customer = Customer::factory()->create();

    $this->actingAs($this->user)
        ->delete("/customers/{$customer->id}")
        ->assertRedirect('/customers');

    expect(Customer::query()->count())->toBe(0)
        ->and(Customer::withTrashed()->whereKey($customer->id)->exists())->toBeTrue();
});
