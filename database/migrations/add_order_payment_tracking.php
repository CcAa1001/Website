<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->boolean('requires_payment')->default(false)->after('payment_status');
            $table->boolean('allow_retry_payment')->default(true)->after('requires_payment');
            $table->string('payment_gateway')->nullable()->after('allow_retry_payment');
        });
    }

    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['requires_payment', 'allow_retry_payment', 'payment_gateway']);
        });
    }
};