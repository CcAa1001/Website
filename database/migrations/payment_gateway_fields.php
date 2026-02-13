
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Add fields to payments table
        Schema::table('payments', function (Blueprint $table) {
            $table->string('gateway', 50)->nullable()->after('payment_method_id');
            $table->text('payment_url')->nullable()->after('gateway_response');
            $table->timestamp('payment_expired_at')->nullable()->after('paid_at');
        });

        // Add fields to outlets table for gateway config
        Schema::table('outlets', function (Blueprint $table) {
            $table->boolean('auto_confirm_kitchen_after_payment')->default(true)->after('service_charge_rate');
            $table->text('nusandana_config')->nullable()->after('auto_confirm_kitchen_after_payment');
        });
    }

    public function down()
    {
        Schema::table('payment_methods', function (Blueprint $table) {
            $table->dropColumn(['requires_online_payment', 'gateway_code', 'gateway_config']);
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn(['gateway', 'gateway_transaction_id', 'gateway_response', 'payment_url', 'payment_expired_at']);
        });

        Schema::table('outlets', function (Blueprint $table) {
            $table->dropColumn(['auto_confirm_kitchen_after_payment', 'nusandana_config']);
        });
    }
};