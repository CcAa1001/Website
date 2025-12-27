<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('products', function (Blueprint $table) {
            // Cek dan tambah kolom description
            if (!Schema::hasColumn('products', 'description')) {
                $table->text('description')->nullable();
            }
            // Cek dan tambah kolom image
            if (!Schema::hasColumn('products', 'image')) {
                $table->string('image')->nullable();
            }
            // Cek dan tambah kolom is_available
            if (!Schema::hasColumn('products', 'is_available')) {
                $table->boolean('is_available')->default(true);
            }
        });
    }

    public function down(): void {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['description', 'image', 'is_available']);
        });
    }
};