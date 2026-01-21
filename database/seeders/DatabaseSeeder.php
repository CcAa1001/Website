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
        // 1. Create Tenant
        $tenant = Tenant::create([
            'code' => 'RESTO-001',
            'name' => 'Restoran Nusantara',
            'business_type' => 'restaurant',
            'email' => 'admin@resto.com',
            'is_active' => true,
        ]);

        // 2. Create Outlet
        $outlet = Outlet::create([
            'tenant_id' => $tenant->id,
            'code' => 'JKT-01',
            'name' => 'Cabang Jakarta Pusat',
            'outlet_type' => 'dine_in',
            'is_active' => true,
        ]);

        // 3. Define Roles & Permissions
        // 'slug' is used for code logic, 'permissions' defines what they can access
        
        // A. MASTER ADMIN (Super Admin) - Has wildcard '*' access
        $roleSuperAdmin = Role::create([
            'tenant_id' => $tenant->id,
            'name' => 'Super Admin',
            'slug' => 'super_admin',
            'permissions' => ['*'], // '*' means Access Everything
            'is_system' => true,
        ]);
        
        // B. MANAGER (Can manage operation, but not system settings)
        $roleManager = Role::create([
            'tenant_id' => $tenant->id,
            'name' => 'Manager',
            'slug' => 'manager',
            'permissions' => [
                'dashboard.access',
                'pos.access',
                'products.manage', // Create/Edit/Delete products
                'categories.manage',
                'customers.manage',
                'reports.view'
            ], 
            'is_system' => false,
        ]);

        // C. CASHIER (POS Only)
        $roleCashier = Role::create([
            'tenant_id' => $tenant->id,
            'name' => 'Cashier',
            'slug' => 'cashier',
            'permissions' => [
                'pos.access',
                'orders.create',
                'orders.view'
            ], 
            'is_system' => false,
        ]);

        // 4. Create MASTER ADMIN User
        User::create([
            'tenant_id' => $tenant->id,
            'outlet_id' => $outlet->id,
            'role_id' => $roleSuperAdmin->id,
            'name' => 'Master Admin',
            'email' => 'admin@admin.com', // Your requested email
            'password' => 'secret', // Will be hashed by model caster or mutator
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        // 5. Create a Dummy Manager (For testing)
        User::create([
            'tenant_id' => $tenant->id,
            'outlet_id' => $outlet->id,
            'role_id' => $roleManager->id,
            'name' => 'John Manager',
            'email' => 'manager@test.com',
            'password' => 'secret',
            'is_active' => true,
        ]);

        // 6. Dummy Data (Products)
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