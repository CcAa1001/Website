<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        // Audit Logs (Mencatat siapa mengubah apa)
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->nullable()->constrained('tenants')->onDelete('cascade');
            $table->foreignUuid('user_id')->nullable()->constrained('users');
            $table->string('action', 50); // create, update, delete
            $table->string('entity_type', 50); // orders, products
            $table->uuid('entity_id')->nullable();
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->ipAddress('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

        // Activity Logs (Log umum sistem)
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('log_name', 50)->default('default');
            $table->text('description');
            $table->nullableUuidMorphs('subject'); // subject_type, subject_id
            $table->nullableUuidMorphs('causer');  // causer_type, causer_id
            $table->json('properties')->nullable();
            $table->uuid('batch_uuid')->nullable();
            $table->timestamps();
        });

        // Notifications (Sistem Notifikasi Internal)
        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type');
            $table->uuidMorphs('notifiable'); // notifiable_type, notifiable_id
            $table->json('data');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });

        // Webhook Endpoints (Integrasi Pihak Ketiga)
        Schema::create('webhook_endpoints', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained('tenants')->onDelete('cascade');
            $table->string('url', 500);
            $table->string('secret')->nullable();
            $table->json('events'); // ["order.created", "payment.paid"]
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_triggered_at')->nullable();
            $table->integer('failure_count')->default(0);
            $table->timestamps();
        });

        // Webhook Logs (Riwayat pengiriman webhook)
        Schema::create('webhook_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('webhook_endpoint_id')->constrained('webhook_endpoints')->onDelete('cascade');
            $table->string('event', 100);
            $table->json('payload');
            $table->integer('response_status')->nullable();
            $table->text('response_body')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down() {
        Schema::dropIfExists('webhook_logs');
        Schema::dropIfExists('webhook_endpoints');
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('activity_logs');
        Schema::dropIfExists('audit_logs');
    }
};