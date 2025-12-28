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
            $table->foreignUuid('user_id')->nullable()->constrained('users');
            
            $table->string('order_number')->unique(); // Contoh: ORD-20241228-0001
            $table->string('order_type'); // dine_in, takeaway, delivery
            $table->string('status')->default('pending');
            
            $table->decimal('subtotal', 15, 2);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('grand_total', 15, 2);
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down() {
        Schema::dropIfExists('orders');
    }
};