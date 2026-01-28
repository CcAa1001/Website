<?php
use Illuminate\Database\Migrations\Migration; use Illuminate\Database\Schema\Blueprint; use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up() {
        // Grup Modifier (Contoh: "Pilihan Sambal")
        Schema::create('modifier_groups', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained('tenants');
            $table->string('name');
            $table->string('selection_type', 20)->default('multiple'); // single/multiple
            $table->integer('min_selections')->default(0);
            $table->integer('max_selections')->nullable();
            $table->boolean('is_required')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Item Modifier (Contoh: "Sambal Ijo", "Sambal Matah")
        Schema::create('modifiers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('modifier_group_id')->constrained('modifier_groups')->onDelete('cascade');
            $table->string('name');
            $table->decimal('price', 15, 2)->default(0);
            $table->decimal('cost_price', 15, 2)->default(0);
            $table->boolean('is_available')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // Link Produk ke Modifier Group
        Schema::create('product_modifier_groups', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('product_id')->constrained('products')->onDelete('cascade');
            $table->foreignUuid('modifier_group_id')->constrained('modifier_groups')->onDelete('cascade');
            $table->integer('sort_order')->default(0);
            $table->timestamp('created_at')->useCurrent();
            $table->unique(['product_id', 'modifier_group_id']);
        });

        // Modifier yang dipilih di Order Item
        Schema::create('order_item_modifiers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('order_item_id')->constrained('order_items')->onDelete('cascade');
            $table->foreignUuid('modifier_id')->constrained('modifiers');
            $table->string('modifier_name'); // Snapshot nama
            $table->integer('quantity')->default(1);
            $table->decimal('unit_price', 15, 2);
            $table->decimal('subtotal', 15, 2);
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down() {
        Schema::dropIfExists('order_item_modifiers');
        Schema::dropIfExists('product_modifier_groups');
        Schema::dropIfExists('modifiers');
        Schema::dropIfExists('modifier_groups');
    }
};