<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use App\Models\Product;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Migrate existing product images to product_images table
        $products = Product::whereNotNull('image_url')->get();

        foreach ($products as $product) {
            DB::table('product_images')->insert([
                'id' => DB::raw('gen_random_uuid()'),
                'product_id' => $product->id,
                'original_path' => $product->image_url,
                'thumbnail_path' => null, // Will be generated on first access
                'medium_path' => null,
                'large_path' => null,
                'webp_path' => null,
                'alt_text' => $product->name,
                'sort_order' => 0,
                'is_primary' => true,
                'original_filename' => basename($product->image_url),
                'created_at' => $product->created_at ?? now(),
                'updated_at' => $product->updated_at ?? now(),
            ]);
        }

        // Log migration
        \Log::info('Migrated ' . $products->count() . ' product images to product_images table');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Delete migrated images (optional - be careful!)
        // DB::table('product_images')->truncate();
    }
};
