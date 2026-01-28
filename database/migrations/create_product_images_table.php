<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('product_images', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('product_id');
            
            // Image paths for different sizes
            $table->string('original_path', 500);
            $table->string('thumbnail_path', 500)->nullable();
            $table->string('medium_path', 500)->nullable();
            $table->string('large_path', 500)->nullable();
            $table->string('webp_path', 500)->nullable();
            
            // Metadata
            $table->string('alt_text')->nullable();
            $table->string('caption', 500)->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_primary')->default(false);
            
            // File info
            $table->string('original_filename')->nullable();
            $table->integer('file_size')->nullable(); // in bytes
            $table->string('mime_type', 50)->nullable();
            $table->integer('width')->nullable();
            $table->integer('height')->nullable();
            
            $table->timestamps();

            // Foreign key
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            
            // Indexes for performance
            $table->index('product_id');
            $table->index(['product_id', 'is_primary']);
            $table->index(['product_id', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_images');
    }
};
