<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        // Suppliers
        Schema::create('suppliers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained('tenants');
            $table->string('code', 20)->nullable();
            $table->string('name');
            $table->string('contact_person')->nullable();
            $table->string('email')->nullable();
            $table->string('phone', 20)->nullable();
            $table->text('address')->nullable();
            $table->integer('payment_terms')->default(30);
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        // Inventory Items (Bahan Baku)
        Schema::create('inventory_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained('tenants');
            $table->foreignUuid('supplier_id')->nullable()->constrained('suppliers');
            $table->string('sku', 50)->nullable();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('unit', 20); // kg, liter, pcs
            $table->decimal('cost_per_unit', 15, 4)->nullable();
            $table->decimal('reorder_level', 15, 4)->nullable();
            $table->decimal('reorder_quantity', 15, 4)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['tenant_id', 'sku']);
        });

        // Outlet Inventory (Stok per Cabang)
        Schema::create('outlet_inventory', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('outlet_id')->constrained('outlets')->onDelete('cascade');
            $table->foreignUuid('inventory_item_id')->constrained('inventory_items')->onDelete('cascade');
            $table->decimal('quantity', 15, 4)->default(0);
            $table->timestamp('last_restock_at')->nullable();
            $table->timestamp('last_count_at')->nullable();
            $table->timestamp('updated_at')->useCurrent();
            
            $table->unique(['outlet_id', 'inventory_item_id']);
        });

        // Product Recipes (Resep)
        Schema::create('product_recipes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('product_id')->constrained('products')->onDelete('cascade');
            $table->foreignUuid('product_variant_id')->nullable()->constrained('product_variants'); // Jika ada varian
            $table->foreignUuid('inventory_item_id')->constrained('inventory_items');
            $table->decimal('quantity', 15, 4); // Jumlah bahan yang dipakai
            $table->timestamps();
        });

        // Stock Movements (Riwayat Keluar Masuk Stok)
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('outlet_id')->constrained('outlets');
            $table->foreignUuid('inventory_item_id')->constrained('inventory_items');
            $table->foreignUuid('user_id')->nullable()->constrained('users');
            
            $table->string('movement_type', 20); // purchase, sale, adjustment, waste
            $table->string('reference_type', 50)->nullable(); // order, po
            $table->uuid('reference_id')->nullable();
            
            $table->decimal('quantity', 15, 4); // Positif (masuk), Negatif (keluar)
            $table->decimal('unit_cost', 15, 4)->nullable();
            $table->decimal('total_cost', 15, 4)->nullable();
            $table->decimal('quantity_before', 15, 4)->nullable();
            $table->decimal('quantity_after', 15, 4)->nullable();
            $table->text('notes')->nullable();
            
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down() {
        Schema::dropIfExists('stock_movements');
        Schema::dropIfExists('product_recipes');
        Schema::dropIfExists('outlet_inventory');
        Schema::dropIfExists('inventory_items');
        Schema::dropIfExists('suppliers');
    }
};