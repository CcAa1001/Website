<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        // Table Areas
        Schema::create('table_areas', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('outlet_id')->constrained('outlets')->onDelete('cascade');
            $table->string('name', 100);
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Tables
        Schema::create('tables', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('outlet_id')->constrained('outlets')->onDelete('cascade');
            $table->foreignUuid('table_area_id')->nullable()->constrained('table_areas')->onDelete('set null');
            $table->string('table_number', 20);
            $table->integer('capacity')->default(4);
            $table->string('status', 20)->default('available');
            $table->uuid('current_order_id')->nullable(); // Nanti diset foreign key manual jika perlu untuk menghindari circular dependency
            $table->string('qr_code')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['outlet_id', 'table_number']);
        });
    }

    public function down() {
        Schema::dropIfExists('tables');
        Schema::dropIfExists('table_areas');
    }
};