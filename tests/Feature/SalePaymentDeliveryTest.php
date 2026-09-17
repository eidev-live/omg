<?php

use App\Enums\DeliveryStatus;
use App\Models\Customer;
use App\Models\SaleType;
use App\Models\User;
use App\Services\PurchaseService;
use App\Services\SaleService;
use Illuminate\Validation\ValidationException;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);

    $this->sales = app(SaleService::class);

    app(PurchaseService::class)->create([
        'purchase_date' => now()->toDateString(),
        'quantity' => 1,
        'egg_per_unit' => 100,
        'total_cost' => 250000,
        'notes' => null,
    ]);

    $this->pack = SaleType::factory()->create(['name' => 'Pack', 'egg_quantity' => 10, 'selling_price' => 33000]);
    $this->customer = Customer::factory()->create();

    $this->sale = $this->sales->create([
        'sale_date' => now()->toDateString(),
        'customer_id' => $this->customer->id,
        'items' => [['sale_type_id' => $this->pack->id, 'quantity' => 2]],
        'paid_amount' => 0,
        'payment_date' => null,
        'delivery_status' => 'PENDING',
        'notes' => null,
    ]);
});

test('payment can be updated and the status is recomputed', function () {
    $this->sales->updatePayment($this->sale, 20000, '2026-09-16');

    expect($this->sale->refresh()->payment_status->value)->toBe('PARTIAL')
        ->and($this->sale->paid_amount)->toBe(20000)
        ->and($this->sale->payment_date?->toDateString())->toBe('2026-09-16');

    $this->sales->updatePayment($this->sale, 66000);
    expect($this->sale->refresh()->payment_status->value)->toBe('PAID');

    $this->sales->updatePayment($this->sale, 0);
    expect($this->sale->refresh()->payment_status->value)->toBe('UNPAID')
        ->and($this->sale->payment_date)->toBeNull();
});

test('payment cannot exceed the transaction total', function () {
    expect(fn () => $this->sales->updatePayment($this->sale, 100000))
        ->toThrow(ValidationException::class);
});

test('payment can be updated through the endpoint', function () {
    $this->patch("/sales/{$this->sale->id}/payment", ['paid_amount' => 66000])->assertRedirect();

    expect($this->sale->refresh()->payment_status->value)->toBe('PAID');
});

test('delivery status updates and manages the delivered timestamp', function () {
    $this->sales->updateDelivery($this->sale, DeliveryStatus::Shipped);
    expect($this->sale->refresh()->delivery_status)->toBe(DeliveryStatus::Shipped)
        ->and($this->sale->delivered_at)->toBeNull();

    $this->sales->updateDelivery($this->sale, DeliveryStatus::Delivered);
    expect($this->sale->refresh()->delivered_at)->not->toBeNull();

    $this->sales->updateDelivery($this->sale, DeliveryStatus::Pending);
    expect($this->sale->refresh()->delivered_at)->toBeNull();
});

test('delivery can be updated through the endpoint', function () {
    $this->patch("/sales/{$this->sale->id}/delivery", ['delivery_status' => 'DELIVERED'])->assertRedirect();

    expect($this->sale->refresh()->delivery_status)->toBe(DeliveryStatus::Delivered);
});

test('a cancelled sale cannot be updated', function () {
    $this->sales->cancel($this->sale);

    expect(fn () => $this->sales->updatePayment($this->sale, 1000))
        ->toThrow(ValidationException::class);

    expect(fn () => $this->sales->updateDelivery($this->sale, DeliveryStatus::Delivered))
        ->toThrow(ValidationException::class);
});
