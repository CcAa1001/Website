<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('products', function (Blueprint $table) {
            // Menambah kolom yang dibutuhkan sistem modern
            if (!Schema::hasColumn('products', 'description')) {
                $table->text('description')->nullable()->after('price');
            }
            if (!Schema::hasColumn('products', 'is_available')) {
                $table->boolean('is_available')->default(true)->after('description');
            }
        });
    }

    public function down(): void {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['description', 'is_available']);
        });
    }
};