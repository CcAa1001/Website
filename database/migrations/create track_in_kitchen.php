<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Adds 'track_in_kitchen' boolean to orders table.
     * - QR scan orders: always true (customer is waiting)
     * - POS orders: operator chooses via "Send to Kitchen Board" toggle
     * - Default: true (safe default — better to track than miss an order)
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->boolean('track_in_kitchen')
                  ->default(true)
                  ->after('order_source')
                  ->index();
        });

        // Backfill: mark all existing QR orders as tracked,
        // and all existing POS orders as NOT tracked (preserves current behavior)
        DB::table('orders')
            ->where('order_source', 'qr_scan')
            ->update(['track_in_kitchen' => true]);

        DB::table('orders')
            ->where('order_source', '!=', 'qr_scan')
            ->whereNull('order_source')
            ->orWhere('order_source', 'mobile_pos')
            ->orWhere('order_source', 'pos')
            ->update(['track_in_kitchen' => false]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('track_in_kitchen');
        });
    }
};