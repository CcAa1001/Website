<?php

namespace App\Http\Livewire\Auth;

use Livewire\Component;
use App\Models\User;
use App\Models\Tenant;
use App\Models\Outlet;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class Register extends Component
{
    public $name = '';
    public $business_name = ''; // Properti baru
    public $email = '';
    public $password = '';

    protected $rules = [
        'name' => 'required|min:3',
        'business_name' => 'required|min:3',
        'email' => 'required|email|unique:users,email',
        'password' => 'required|min:6',
    ];

    public function store()
    {
        $this->validate();

        DB::beginTransaction();
        try {
            // 1. Buat Tenant
            $tenant = Tenant::create([
                'code' => strtoupper(Str::slug($this->business_name)) . '-' . rand(100,999),
                'name' => $this->business_name,
                'email' => $this->email,
                'is_active' => true,
            ]);

            // 2. Buat Outlet
            $outlet = Outlet::create([
                'tenant_id' => $tenant->id,
                'code' => 'HQ-01',
                'name' => 'Headquarters',
                'is_active' => true,
            ]);

            // 3. Buat Role Owner
            $role = Role::create([
                'tenant_id' => $tenant->id,
                'name' => 'Owner',
                'slug' => 'owner',
                'permissions' => ['*'],
                'is_system' => true,
            ]);

            // 4. Buat User (FIX: Password di-Hash)
            $user = User::create([
                'tenant_id' => $tenant->id,
                'outlet_id' => $outlet->id,
                'role_id' => $role->id,
                'name' => $this->name,
                'email' => $this->email,
                'password' => Hash::make($this->password), // INI WAJIB
                'is_active' => true,
            ]);

            DB::commit();

            auth()->login($user, true);
            return redirect()->to('/dashboard');

        } catch (\Exception $e) {
            DB::rollBack();
            $this->addError('email', 'Registration failed: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.auth.register')->layout('layouts.base');
    }
}