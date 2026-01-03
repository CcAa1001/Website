<?php
use Illuminate\Database\Migrations\Migration; use Illuminate\Database\Schema\Blueprint; use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up() {
        // Kitchen Stations (Ex: Grill Station, Bar, Dessert)
        Schema::create('kitchen_stations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('outlet_id')->constrained('outlets')->onDelete('cascade');
            $table->string('name', 100);
            $table->json('printer_config')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Mapping Produk ke Station (Satu produk bisa ke beberapa station)
        Schema::create('product_kitchen_stations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('product_id')->constrained('products')->onDelete('cascade');
            $table->foreignUuid('kitchen_station_id')->constrained('kitchen_stations')->onDelete('cascade');
            $table->timestamp('created_at')->useCurrent();
            $table->unique(['product_id', 'kitchen_station_id']);
        });

        // Kitchen Orders (Tiket Dapur)
        Schema::create('kitchen_orders', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('order_id')->constrained('orders')->onDelete('cascade');
            $table->foreignUuid('kitchen_station_id')->constrained('kitchen_stations');
            $table->string('status', 20)->default('pending'); // pending, cooking, ready
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }
    public function down() {
        Schema::dropIfExists('kitchen_orders');
        Schema::dropIfExists('product_kitchen_stations');
        Schema::dropIfExists('kitchen_stations');
    }
};