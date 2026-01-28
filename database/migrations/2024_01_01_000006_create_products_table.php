<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('products', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained('tenants')->onDelete('cascade');
            $table->foreignUuid('category_id')->nullable()->constrained('categories')->onDelete('set null');
            $table->string('sku', 50)->nullable();
            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->string('image_url', 500)->nullable();
            $table->decimal('base_price', 15, 2);
            $table->decimal('cost_price', 15, 2)->nullable();
            $table->boolean('tax_inclusive')->default(true);
            $table->boolean('is_taxable')->default(true);
            $table->string('product_type', 50)->default('single');
            $table->integer('preparation_time')->default(15);
            $table->integer('calories')->nullable();
            $table->boolean('is_available')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->integer('sort_order')->default(0);
            $table->json('tags')->nullable(); // Array tags
            $table->json('allergens')->nullable(); // Array allergens
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['tenant_id', 'slug']);
            $table->index(['tenant_id', 'sku']);
        });
    }

    public function down() {
        Schema::dropIfExists('products');
    }
};