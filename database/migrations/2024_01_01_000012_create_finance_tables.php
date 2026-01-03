<?php
use Illuminate\Database\Migrations\Migration; use Illuminate\Database\Schema\Blueprint; use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up() {
        // Payment Methods (Metode Pembayaran)
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained('tenants');
            $table->string('code', 50); // cash, qris, etc
            $table->string('name', 100);
            $table->string('payment_type', 20); // cash, card, ewallet
            $table->json('gateway_config')->nullable();
            $table->decimal('processing_fee_percentage', 5, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['tenant_id', 'code']);
        });

        // Shifts (Sesi Kasir)
        Schema::create('shifts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('outlet_id')->constrained('outlets');
            $table->foreignUuid('user_id')->constrained('users');
            $table->string('shift_number', 50);
            
            $table->decimal('opening_cash', 15, 2);
            $table->decimal('expected_cash', 15, 2)->nullable();
            $table->decimal('actual_cash', 15, 2)->nullable();
            $table->decimal('cash_difference', 15, 2)->nullable();
            
            $table->decimal('total_sales', 15, 2)->default(0);
            $table->json('payment_breakdown')->default(new \Illuminate\Database\Query\Expression("('{}')")); // JSON Object
            
            $table->string('status', 20)->default('open'); // open, closed
            $table->text('notes')->nullable();
            
            $table->timestamp('opened_at')->useCurrent();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();
        });

        // Cash Movements (Uang Masuk/Keluar Laci Kasir)
        Schema::create('cash_movements', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('shift_id')->constrained('shifts');
            $table->foreignUuid('user_id')->constrained('users');
            $table->string('movement_type', 20); // cash_in, cash_out
            $table->decimal('amount', 15, 2);
            $table->text('reason');
            $table->timestamp('created_at')->useCurrent();
        });

        // Payments (Transaksi Pembayaran)
        Schema::create('payments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained('tenants');
            $table->foreignUuid('outlet_id')->constrained('outlets');
            $table->foreignUuid('order_id')->constrained('orders');
            $table->foreignUuid('payment_method_id')->constrained('payment_methods');
            $table->foreignUuid('user_id')->nullable()->constrained('users');
            
            $table->string('payment_number', 50);
            $table->string('transaction_type', 20)->default('payment'); // payment, refund
            
            $table->decimal('amount', 15, 2);
            $table->decimal('net_amount', 15, 2);
            
            $table->string('gateway_transaction_id')->nullable();
            $table->json('gateway_response')->nullable();
            
            $table->string('status', 20)->default('pending');
            $table->decimal('cash_received', 15, 2)->nullable();
            $table->decimal('cash_change', 15, 2)->nullable();
            
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });
    }

    public function down() {
        Schema::dropIfExists('payments');
        Schema::dropIfExists('cash_movements');
        Schema::dropIfExists('shifts');
        Schema::dropIfExists('payment_methods');
    }
};