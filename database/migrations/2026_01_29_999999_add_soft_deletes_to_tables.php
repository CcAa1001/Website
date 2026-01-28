<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Tambahkan kolom deleted_at ke tabel 'tables'
        Schema::table('tables', function (Blueprint $table) {
            if (!Schema::hasColumn('tables', 'deleted_at')) {
                $table->softDeletes();
            }
        });
        
        // Tambahkan juga ke users jika belum ada (karena User.php pakai SoftDeletes juga)
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'deleted_at')) {
                $table->softDeletes();
            }
        });
    }

    public function down()
    {
        Schema::table('tables', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
        Schema::table('users', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};