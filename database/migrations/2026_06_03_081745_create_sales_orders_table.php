<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales_orders', function (Blueprint $table) {
            $table->id();
            $table->string('customer_name');
            $table->string('customer_email');
            $table->string('customer_phone')->nullable();
            $table->foreignId('warehouse_id')->constrained()->cascadeOnDelete();
            $table->string('status');
            $table->decimal('total_amount', 10, 2);
            $table->ulid('transaction_reference')->nullable();
            $table->json('payment_payload')->nullable();
            $table->foreignId('creator_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->index(['status', 'created_at', 'transaction_reference']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_orders');
    }
};
