<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Tenant;
use App\Models\Outlet;
use App\Models\Role;
use App\Models\User;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        // 1. Buat Tenant (Restoran Utama)
        $tenant = Tenant::create([
            'code' => 'RESTO-001',
            'name' => 'Restoran Nusantara',
            'business_type' => 'restaurant',
            'email' => 'admin@resto.com',
            'is_active' => true,
        ]);

        // 2. Buat Outlet (Cabang Pusat)
        $outlet = Outlet::create([
            'tenant_id' => $tenant->id,
            'code' => 'JKT-01',
            'name' => 'Cabang Jakarta Pusat',
            'outlet_type' => 'dine_in',
            'is_active' => true,
        ]);

        // 3. Buat Roles
        $roleSuperAdmin = Role::create([
            'tenant_id' => $tenant->id,
            'name' => 'Super Admin',
            'slug' => 'super_admin',
            'permissions' => ['*'], // Akses semua
            'is_system' => true,
        ]);
        
        $roleCashier = Role::create([
            'tenant_id' => $tenant->id,
            'name' => 'Cashier',
            'slug' => 'cashier',
            'permissions' => ['pos.access', 'orders.create'], 
        ]);

        // 4. Buat User Admin (Untuk Login Pertama)
        User::create([
            'tenant_id' => $tenant->id,
            'outlet_id' => $outlet->id,
            'role_id' => $roleSuperAdmin->id,
            'name' => 'Admin Utama',
            'email' => 'admin@material.com', // Email default template material dashboard
            'password' => 'secret', // Password akan di-hash otomatis oleh Model User (setPasswordAttribute)
            'is_active' => true,
        ]);

        // 5. Data Dummy Produk (Agar Menu tidak kosong)
        $catFood = Category::create([
            'tenant_id' => $tenant->id,
            'name' => 'Makanan Utama',
            'slug' => 'makanan-utama',
            'is_active' => true
        ]);

        Product::create([
            'tenant_id' => $tenant->id,
            'category_id' => $catFood->id,
            'name' => 'Nasi Goreng Spesial',
            'slug' => 'nasi-goreng-spesial',
            'base_price' => 35000,
            'is_available' => true,
        ]);
    }
}