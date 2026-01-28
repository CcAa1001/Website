<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // 1. Tabel Master Permission
        if (!Schema::hasTable('permissions')) {
            Schema::create('permissions', function (Blueprint $table) {
                $table->id();
                $table->string('name')->unique(); // Contoh: 'view_reports'
                $table->string('label')->nullable(); // Contoh: 'Melihat Laporan'
                $table->string('group')->nullable(); // Contoh: 'laporan', 'pos'
                $table->timestamps();
            });
        }

        // 2. Tabel Pivot User <-> Permission (Untuk Checklist Manual)
        if (!Schema::hasTable('permission_user')) {
            Schema::create('permission_user', function (Blueprint $table) {
                $table->id();
                // Sesuaikan tipe ID user dengan tabel users (UUID atau BigInt)
                // Karena kita pakai UUID di User.php, gunakan uuid disini
                $table->foreignUuid('user_id')->constrained()->onDelete('cascade');
                $table->foreignId('permission_id')->constrained()->onDelete('cascade');
                $table->timestamps();
            });
        }
        
        // 3. Seeder Awal (Opsional, agar tidak kosong)
        // Kita masukkan langsung disini agar praktis saat migrate
        DB::table('permissions')->insertOrIgnore([
            ['name' => 'access_pos', 'label' => 'Akses Mesin Kasir', 'group' => 'operational'],
            ['name' => 'access_kitchen', 'label' => 'Akses Layar Dapur', 'group' => 'operational'],
            ['name' => 'manage_products', 'label' => 'Kelola Produk & Harga', 'group' => 'catalog'],
            ['name' => 'manage_inventory', 'label' => 'Kelola Stok Bahan', 'group' => 'catalog'],
            ['name' => 'view_reports', 'label' => 'Melihat Laporan Keuangan', 'group' => 'report'],
            ['name' => 'manage_users', 'label' => 'Kelola Karyawan', 'group' => 'admin'],
        ]);
    }

    public function down()
    {
        Schema::dropIfExists('permission_user');
        Schema::dropIfExists('permissions');
    }
};