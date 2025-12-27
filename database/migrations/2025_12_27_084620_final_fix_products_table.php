<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('products', function (Blueprint $table) {
            // Cek satu per satu sebelum menambah agar tidak error "column already exists"
            if (!Schema::hasColumn('products', 'description')) {
                $table->text('description')->nullable();
            }
            if (!Schema::hasColumn('products', 'is_available')) {
                $table->boolean('is_available')->default(true);
            }
        });
    }

    public function down(): void {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['description', 'is_available']);
        });
    }
};