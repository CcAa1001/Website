<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Models\Tenant;
use App\Models\Outlet;
use App\Models\Role;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        // 1. Buat Tenant (Perusahaan)
        $tenant = Tenant::create([
            'code' => 'ADMIN-HQ',
            'name' => 'Admin Headquarters',
            'email' => 'admin@secret.com',
            'is_active' => true,
        ]);

        // 2. Buat Outlet Default
        $outlet = Outlet::create([
            'tenant_id' => $tenant->id,
            'code' => 'HQ-01',
            'name' => 'Main Office',
            'is_active' => true,
        ]);

        // 3. Buat Role Super Admin
        $role = Role::create([
            'tenant_id' => $tenant->id,
            'name' => 'Super Admin',
            'slug' => 'super-admin',
            'permissions' => ['*'], // Akses ke semua fitur
            'is_system' => true,
        ]);

        // 4. Buat User Admin (INI AKUN ANDA)
        User::create([
            'tenant_id' => $tenant->id,
            'outlet_id' => $outlet->id,
            'role_id' => $role->id,
            'name' => 'Super Administrator',
            'email' => 'admin@admin.com', // Email Login
            'password' => Hash::make('secret'), // Password Login (Ganti 'secret' dengan password pilihan Anda)
            'is_active' => true,
            'email_verified_at' => now(),
        ]);
        
        // Panggil seeder lain jika perlu
        // $this->call([TableSeeder::class]); 
    }
}