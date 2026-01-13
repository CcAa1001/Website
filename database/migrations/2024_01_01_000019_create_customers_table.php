<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('customers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained('tenants')->onDelete('cascade');
            $table->string('customer_code', 20)->nullable();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone', 20)->nullable();
            $table->timestamp('phone_verified_at')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('gender', 10)->nullable();
            $table->text('address')->nullable();
            $table->string('city', 100)->nullable();
            $table->string('postal_code', 10)->nullable();
            $table->integer('loyalty_points')->default(0);
            $table->string('loyalty_tier', 20)->default('bronze');
            $table->integer('total_orders')->default(0);
            $table->decimal('total_spent', 15, 2)->default(0);
            $table->text('notes')->nullable();
            $table->json('tags')->nullable(); // Pastikan database support JSON (Postgres/MySQL 5.7+)
            $table->boolean('marketing_consent')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['tenant_id', 'customer_code']);
        });
    }

    public function down() {
        Schema::dropIfExists('customers');
    }
};