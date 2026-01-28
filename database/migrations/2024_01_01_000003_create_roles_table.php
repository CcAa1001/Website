<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('roles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->nullable()->constrained('tenants')->onDelete('cascade');
            $table->string('name', 50);
            $table->string('slug', 50);
            $table->json('permissions')->nullable(); // JSONB
            $table->boolean('is_system')->default(false);
            $table->timestamps();
        });
    }

    public function down() { Schema::dropIfExists('roles'); }
};