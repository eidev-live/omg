<?php

namespace Database\Seeders;

use App\Enums\DeliveryStatus;
use App\Models\Customer;
use App\Models\Purchase;
use App\Models\SaleType;
use App\Services\PurchaseService;
use App\Services\SaleService;
use Illuminate\Database\Seeder;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        if (Purchase::query()->exists()) {
            return;
        }

        $budi = Customer::query()->firstOrCreate(
            ['name' => 'Budi'],
            ['phone' => '081234567890', 'location' => 'Jakarta', 'is_active' => true],
        );

        $sari = Customer::query()->firstOrCreate(
            ['name' => 'Sari'],
            ['phone' => '081298765432', 'location' => 'Bekasi', 'is_active' => true],
        );

        $andi = Customer::query()->firstOrCreate(
            ['name' => 'Andi'],
            ['phone' => '081377788899', 'location' => 'Depok', 'is_active' => true],
        );

        $pack = SaleType::query()->firstOrCreate(['name' => 'Pack'], ['egg_quantity' => 10, 'selling_price' => 33000, 'is_active' => true]);
        $tray = SaleType::query()->firstOrCreate(['name' => 'Tray'], ['egg_quantity' => 30, 'selling_price' => 91000, 'is_active' => true]);
        $ikat = SaleType::query()->firstOrCreate(['name' => 'Ikat'], ['egg_quantity' => 180, 'selling_price' => 510000, 'is_active' => true]);

        $purchases = app(PurchaseService::class);
        $sales = app(SaleService::class);

        // Dua batch dengan HPP berbeda, untuk mendemonstrasikan FIFO.
        $purchases->create([
            'purchase_date' => now()->subDays(16)->toDateString(),
            'quantity' => 1,
            'egg_per_unit' => 180,
            'total_cost' => 475000,
            'notes' => 'Batch awal',
        ]);

        $purchases->create([
            'purchase_date' => now()->subDays(10)->toDateString(),
            'quantity' => 2,
            'egg_per_unit' => 180,
            'total_cost' => 1000000,
            'notes' => 'Batch kedua',
        ]);

        // 1) Lunas dan terkirim.
        $sales->create([
            'sale_date' => now()->subDays(9)->toDateString(),
            'customer_id' => $budi->id,
            'items' => [['sale_type_id' => $pack->id, 'quantity' => 2]],
            'paid_amount' => 66000,
            'payment_date' => now()->subDays(9)->toDateString(),
            'delivery_status' => DeliveryStatus::Delivered->value,
            'notes' => null,
        ]);

        // 2) Pembayaran sebagian dan sedang dikirim.
        $partial = $sales->create([
            'sale_date' => now()->subDays(7)->toDateString(),
            'customer_id' => $sari->id,
            'items' => [['sale_type_id' => $tray->id, 'quantity' => 1]],
            'paid_amount' => 50000,
            'payment_date' => now()->subDays(7)->toDateString(),
            'delivery_status' => DeliveryStatus::Pending->value,
            'notes' => 'Sisa dibayar saat pengiriman',
        ]);
        $sales->updateDelivery($partial, DeliveryStatus::Shipped);

        // 3) Belum dibayar, mendemonstrasikan FIFO lintas dua layer.
        $sales->create([
            'sale_date' => now()->subDays(5)->toDateString(),
            'customer_id' => $andi->id,
            'items' => [['sale_type_id' => $ikat->id, 'quantity' => 1]],
            'paid_amount' => 0,
            'payment_date' => null,
            'delivery_status' => DeliveryStatus::Pending->value,
            'notes' => 'Pesanan besar',
        ]);

        // 4) Lunas dan terkirim (multi item).
        $sales->create([
            'sale_date' => now()->subDays(3)->toDateString(),
            'customer_id' => $budi->id,
            'items' => [
                ['sale_type_id' => $pack->id, 'quantity' => 1],
                ['sale_type_id' => $tray->id, 'quantity' => 1],
            ],
            'paid_amount' => 124000,
            'payment_date' => now()->subDays(3)->toDateString(),
            'delivery_status' => DeliveryStatus::Delivered->value,
            'notes' => null,
        ]);

        // 5) Belum dibayar dan sudah dikirim.
        $lastSale = $sales->create([
            'sale_date' => now()->subDays(1)->toDateString(),
            'customer_id' => $sari->id,
            'items' => [['sale_type_id' => $pack->id, 'quantity' => 1]],
            'paid_amount' => 0,
            'payment_date' => null,
            'delivery_status' => DeliveryStatus::Pending->value,
            'notes' => null,
        ]);
        $sales->updateDelivery($lastSale, DeliveryStatus::Shipped);
    }
}
