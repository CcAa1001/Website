<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('categories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained('tenants')->onDelete('cascade');
            $table->uuid('parent_id')->nullable(); // Self-referencing FK ditambahkan nanti atau nullable
            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->string('image_url', 500)->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
            
            $table->unique(['tenant_id', 'slug']);
            // FK parent_id sebaiknya ditambahkan via alter table jika tabel ini mereferensi dirinya sendiri untuk menghindari error saat create
        });
        
        Schema::table('categories', function (Blueprint $table) {
             $table->foreign('parent_id')->references('id')->on('categories')->onDelete('set null');
        });
    }

    public function down() {
        Schema::dropIfExists('categories');
    }
};