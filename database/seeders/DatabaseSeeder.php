<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Tenant;
use App\Models\Outlet;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        // 1. Buat Tenant Pertama
        $tenant = Tenant::create([
            'id' => (string) Str::uuid(),
            'code' => 'T001',
            'name' => 'Bakery Center'
        ]);

        // 2. Buat Outlet Pertama
        $outlet = Outlet::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $tenant->id,
            'code' => 'O001',
            'name' => 'Main Branch'
        ]);

        // 3. Buat User Admin yang Terhubung ke Tenant & Outlet
        User::factory()->create([
            'tenant_id' => $tenant->id,
            'outlet_id' => $outlet->id,
            'name' => 'Admin',
            'email' => 'admin@material.com',
            'password' => 'secret' // Pastikan model User sudah ada setPasswordAttribute atau gunakan bcrypt('secret')
        ]);
    }
}