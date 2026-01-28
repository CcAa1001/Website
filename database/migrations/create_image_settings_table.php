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
        Schema::create('image_settings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->string('setting_key', 100)->index();
            $table->text('setting_value')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();

            // Foreign key
            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            
            // Unique constraint: one setting per tenant
            $table->unique(['tenant_id', 'setting_key']);
        });

        // Insert default settings
        DB::table('image_settings')->insert([
            [
                'id' => DB::raw('gen_random_uuid()'),
                'tenant_id' => DB::raw('(SELECT id FROM tenants LIMIT 1)'),
                'setting_key' => 'product_placeholder_url',
                'setting_value' => '/assets/images/product-placeholder.png',
                'description' => 'Default placeholder image for products without images',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => DB::raw('gen_random_uuid()'),
                'tenant_id' => DB::raw('(SELECT id FROM tenants LIMIT 1)'),
                'setting_key' => 'max_upload_size_mb',
                'setting_value' => '2',
                'description' => 'Maximum upload size in megabytes',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => DB::raw('gen_random_uuid()'),
                'tenant_id' => DB::raw('(SELECT id FROM tenants LIMIT 1)'),
                'setting_key' => 'allowed_formats',
                'setting_value' => 'jpg,jpeg,png,webp',
                'description' => 'Comma-separated list of allowed image formats',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => DB::raw('gen_random_uuid()'),
                'tenant_id' => DB::raw('(SELECT id FROM tenants LIMIT 1)'),
                'setting_key' => 'thumbnail_width',
                'setting_value' => '150',
                'description' => 'Thumbnail width in pixels',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => DB::raw('gen_random_uuid()'),
                'tenant_id' => DB::raw('(SELECT id FROM tenants LIMIT 1)'),
                'setting_key' => 'thumbnail_height',
                'setting_value' => '150',
                'description' => 'Thumbnail height in pixels',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => DB::raw('gen_random_uuid()'),
                'tenant_id' => DB::raw('(SELECT id FROM tenants LIMIT 1)'),
                'setting_key' => 'medium_width',
                'setting_value' => '600',
                'description' => 'Medium image width in pixels',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => DB::raw('gen_random_uuid()'),
                'tenant_id' => DB::raw('(SELECT id FROM tenants LIMIT 1)'),
                'setting_key' => 'medium_height',
                'setting_value' => '600',
                'description' => 'Medium image height in pixels',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => DB::raw('gen_random_uuid()'),
                'tenant_id' => DB::raw('(SELECT id FROM tenants LIMIT 1)'),
                'setting_key' => 'large_width',
                'setting_value' => '1200',
                'description' => 'Large image width in pixels',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => DB::raw('gen_random_uuid()'),
                'tenant_id' => DB::raw('(SELECT id FROM tenants LIMIT 1)'),
                'setting_key' => 'large_height',
                'setting_value' => '1200',
                'description' => 'Large image height in pixels',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => DB::raw('gen_random_uuid()'),
                'tenant_id' => DB::raw('(SELECT id FROM tenants LIMIT 1)'),
                'setting_key' => 'jpeg_quality',
                'setting_value' => '85',
                'description' => 'JPEG compression quality (1-100)',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => DB::raw('gen_random_uuid()'),
                'tenant_id' => DB::raw('(SELECT id FROM tenants LIMIT 1)'),
                'setting_key' => 'webp_quality',
                'setting_value' => '80',
                'description' => 'WebP compression quality (1-100)',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => DB::raw('gen_random_uuid()'),
                'tenant_id' => DB::raw('(SELECT id FROM tenants LIMIT 1)'),
                'setting_key' => 'enable_webp',
                'setting_value' => '1',
                'description' => 'Enable automatic WebP conversion',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('image_settings');
    }
};
