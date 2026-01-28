<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        // Outlet Products (Override harga/stok per cabang)
        Schema::create('outlet_products', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('outlet_id')->constrained('outlets')->onDelete('cascade');
            $table->foreignUuid('product_id')->constrained('products')->onDelete('cascade');
            $table->decimal('price_override', 15, 2)->nullable();
            $table->boolean('is_available')->default(true);
            $table->integer('stock_quantity')->nullable();
            $table->integer('low_stock_threshold')->default(10);
            $table->timestamps();
            
            $table->unique(['outlet_id', 'product_id']);
        });

        // Refunds (Pengembalian Dana)
        Schema::create('refunds', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('payment_id')->constrained('payments')->onDelete('cascade');
            $table->foreignUuid('user_id')->nullable()->constrained('users');
            $table->string('refund_number', 50);
            $table->decimal('amount', 15, 2);
            $table->text('reason');
            $table->string('status', 20)->default('pending');
            $table->string('gateway_refund_id')->nullable();
            $table->json('gateway_response')->nullable();
            $table->timestamp('refunded_at')->nullable();
            $table->timestamps();
        });
    }

    public function down() {
        Schema::dropIfExists('refunds');
        Schema::dropIfExists('outlet_products');
    }
};