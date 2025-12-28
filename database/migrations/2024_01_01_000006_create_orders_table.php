<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        // Membuat Tabel Orders
        Schema::create('orders', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained('tenants');
            $table->foreignUuid('outlet_id')->constrained('outlets');
            $table->foreignUuid('user_id')->nullable()->constrained('users'); 
            
            $table->string('order_number')->unique();
            $table->string('order_type')->default('dine_in'); // dari file 000004
            $table->string('status')->default('pending');     // dari file 000004
            
            $table->decimal('subtotal', 15, 2);
            $table->decimal('tax_amount', 15, 2);
            $table->decimal('grand_total', 15, 2);
            $table->timestamps();
            $table->softDeletes(); // Menambahkan fitur soft delete
        });

        // Membuat Tabel Order Items
        Schema::create('order_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('order_id')->constrained('orders')->onDelete('cascade');
            $table->foreignUuid('product_id')->constrained('products');
            $table->string('product_name');
            $table->integer('quantity');
            $table->decimal('unit_price', 15, 2);
            $table->decimal('subtotal', 15, 2);
            $table->timestamps();
        });
    }

    public function down() {
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
};