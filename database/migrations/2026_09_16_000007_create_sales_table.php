<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique();
            $table->date('sale_date')->index();
            $table->foreignId('customer_id')->constrained()->restrictOnDelete();
            $table->unsignedBigInteger('total_amount');
            $table->string('payment_status')->default('UNPAID')->index();
            $table->unsignedBigInteger('paid_amount')->default(0);
            $table->date('payment_date')->nullable();
            $table->string('delivery_status')->default('PENDING')->index();
            $table->timestamp('delivered_at')->nullable();
            $table->text('notes')->nullable();
            $table->string('status')->default('ACTIVE')->index();
            $table->timestamp('cancelled_at')->nullable();
            $table->string('cancel_reason')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};
