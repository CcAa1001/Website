<?php

namespace App\Http\Livewire\Auth;

use Livewire\Component;
use App\Models\User;
use App\Models\Tenant;
use App\Models\Outlet;
use Illuminate\Support\Facades\Hash;

class Register extends Component
{
    public $name = '';
    public $email = '';
    public $password = '';

    protected $rules = [
        'name' => 'required|min:3',
        'email' => 'required|email|unique:users,email',
        'password' => 'required|min:6',
    ];

    public function store()
    {
        $this->validate();

        \Illuminate\Support\Facades\DB::beginTransaction(); 
        try {
            // 1. Buat Tenant Baru (atau cari yang sudah ada jika sistemnya shared)
            $tenant = \App\Models\Tenant::create([
                'code' => strtoupper(\Illuminate\Support\Str::slug($this->business_name)) . '-' . rand(100,999),
                'name' => $this->business_name,
                'email' => $this->email,
                'is_active' => true,
            ]);

            // 2. Buat Outlet Default untuk Tenant tersebut
            $outlet = \App\Models\Outlet::create([
                'tenant_id' => $tenant->id,
                'code' => 'HQ-01',
                'name' => 'Pusat',
                'is_active' => true,
            ]);

            // 3. Buat Role 'Owner' untuk Tenant tersebut
            $role = \App\Models\Role::create([
                'tenant_id' => $tenant->id,
                'name' => 'Owner',
                'slug' => 'owner',
                'permissions' => ['*'],
                'is_system' => true,
            ]);

            // 4. Buat User dengan menghubungkan Tenant, Outlet, dan Role
            $user = \App\Models\User::create([
                'tenant_id' => $tenant->id,
                'outlet_id' => $outlet->id,
                'role_id' => $role->id, // INI WAJIB ADA agar tidak error
                'name' => $this->name,
                'email' => $this->email,
                'password' => $this->password, // Jangan di-hash di sini (baca poin 2)
                'is_active' => true,
            ]);

            \Illuminate\Support\Facades\DB::commit();

            auth()->login($user, true);
            return redirect()->to('/dashboard');

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            $this->addError('email', 'Gagal mendaftar: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.auth.register')->layout('layouts.base');
    }
}