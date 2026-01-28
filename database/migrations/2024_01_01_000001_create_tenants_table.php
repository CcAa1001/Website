<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('tenants', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code', 20)->unique();
            $table->string('name');
            $table->string('legal_name')->nullable();
            $table->string('tax_id', 50)->nullable(); // NPWP
            $table->string('business_type', 50)->default('restaurant');
            $table->string('logo_url', 500)->nullable();
            $table->string('email')->nullable();
            $table->string('phone', 20)->nullable();
            $table->text('address')->nullable();
            $table->string('city', 100)->nullable();
            $table->string('province', 100)->nullable();
            $table->string('postal_code', 10)->nullable();
            $table->string('country', 50)->default('Indonesia');
            $table->string('currency', 3)->default('IDR');
            $table->string('timezone', 50)->default('Asia/Jakarta');
            $table->string('subscription_plan', 50)->default('basic');
            $table->timestamp('subscription_expires_at')->nullable();
            $table->json('settings')->nullable(); // Menggunakan JSONB di Postgres
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index('is_active');
        });
    }

    public function down() { Schema::dropIfExists('tenants'); }
};