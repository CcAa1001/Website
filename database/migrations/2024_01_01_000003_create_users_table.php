<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('users', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->nullable()->constrained('tenants');
            $table->foreignUuid('outlet_id')->nullable()->constrained('outlets');
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable(); 
            $table->string('password');
            $table->string('phone', 20)->nullable();
            $table->rememberToken();
            $table->timestamps();
        });
    }

    public function down() { Schema::dropIfExists('users'); }
};