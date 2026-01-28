<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('orders', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained('tenants');
            $table->foreignUuid('outlet_id')->constrained('outlets');
            $table->uuid('customer_id')->nullable(); // Referensi ke tabel customers
            $table->uuid('table_id')->nullable();    // Referensi ke tabel tables
            $table->foreignUuid('user_id')->nullable()->constrained('users');
            
            // Order Identification
            $table->string('order_number', 50);
            $table->string('order_type', 20); // dine_in, takeaway, etc
            $table->string('order_source', 20)->default('pos');
            $table->string('external_order_id', 100)->nullable();
            
            // Customer Info (Snapshot)
            $table->string('customer_name')->nullable();
            $table->string('customer_phone', 20)->nullable();
            $table->string('customer_email')->nullable();
            
            // Delivery Info
            $table->uuid('delivery_address_id')->nullable();
            $table->text('delivery_address')->nullable();
            $table->text('delivery_notes')->nullable();
            $table->decimal('delivery_fee', 15, 2)->default(0);
            $table->timestamp('estimated_delivery_time')->nullable();

            // Status
            $table->string('status', 20)->default('pending');
            $table->string('payment_status', 20)->default('unpaid');
            
            // Pricing
            $table->decimal('subtotal', 15, 2);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('service_charge', 15, 2)->default(0);
            $table->decimal('rounding_amount', 15, 2)->default(0);
            $table->decimal('grand_total', 15, 2);
            
            // Info
            $table->text('notes')->nullable();
            $table->integer('guest_count')->default(1);
            
            // Timestamps
            $table->timestamp('ordered_at')->useCurrent();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('prepared_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->text('cancellation_reason')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('order_number');
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('order_id')->constrained('orders')->onDelete('cascade');
            $table->foreignUuid('product_id')->constrained('products');
            $table->uuid('product_variant_id')->nullable(); // Referensi ke product_variants
            
            $table->string('product_name');
            $table->string('variant_name', 100)->nullable();
            $table->string('sku', 50)->nullable();
            
            $table->integer('quantity')->default(1);
            $table->decimal('unit_price', 15, 2);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('subtotal', 15, 2);
            
            $table->text('notes')->nullable();
            
            $table->string('kitchen_status', 20)->default('pending');
            $table->timestamp('kitchen_printed_at')->nullable();
            $table->timestamp('prepared_at')->nullable();
            $table->timestamp('served_at')->nullable();
            
            $table->timestamps();
        });
    }

    public function down() {
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
};