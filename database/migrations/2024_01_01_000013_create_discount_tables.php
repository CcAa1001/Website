<?php
use Illuminate\Database\Migrations\Migration; use Illuminate\Database\Schema\Blueprint; use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up() {
        Schema::create('discounts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained('tenants');
            $table->string('code', 50)->nullable();
            $table->string('name');
            $table->string('discount_type', 20); // percentage, fixed
            $table->decimal('discount_value', 15, 2);
            $table->decimal('min_order_amount', 15, 2)->nullable();
            $table->decimal('max_discount_amount', 15, 2)->nullable();
            
            $table->timestamp('valid_from')->nullable();
            $table->timestamp('valid_until')->nullable();
            $table->integer('usage_limit')->nullable();
            $table->integer('current_usage')->default(0);
            
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('order_discounts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('order_id')->constrained('orders')->onDelete('cascade');
            $table->foreignUuid('discount_id')->nullable()->constrained('discounts');
            $table->string('discount_name');
            $table->decimal('discount_amount', 15, 2);
            $table->timestamp('created_at')->useCurrent();
        });
    }
    public function down() {
        Schema::dropIfExists('order_discounts');
        Schema::dropIfExists('discounts');
    }
};