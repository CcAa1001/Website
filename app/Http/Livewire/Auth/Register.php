<?php

namespace App\Http\Livewire\Auth;

use Livewire\Component;
use App\Models\User;
use App\Models\Tenant;
use App\Models\Outlet;
use App\Models\Role;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class Register extends Component
{
    public $name = '';
    public $email = '';
    public $password = '';
    public $business_name = '';

    protected $rules = [
        'name' => 'required|min:3',
        'email' => 'required|email:rfc,dns|unique:users',
        'password' => 'required|min:6',
        'business_name' => 'required|min:3',
    ];

    // UBAH DARI 'register' MENJADI 'store'
    public function store()
    {
        $this->validate();

        DB::beginTransaction(); 
        try {
            // 1. Buat Tenant Baru
            $tenant = Tenant::create([
                'code' => strtoupper(Str::slug($this->business_name)) . '-' . rand(100,999),
                'name' => $this->business_name,
                'email' => $this->email,
                'is_active' => true,
            ]);

            // 2. Buat Outlet Pertama
            $outlet = Outlet::create([
                'tenant_id' => $tenant->id,
                'code' => 'HQ-01',
                'name' => 'Pusat',
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

            // 4. Buat User
            $user = User::create([
                'tenant_id' => $tenant->id,
                'outlet_id' => $outlet->id,
                'role_id' => $role->id,
                'name' => $this->name,
                'email' => $this->email,
                'password' => $this->password, 
                'is_active' => true,
            ]);

            DB::commit();

            auth()->login($user, true);
            return redirect()->to('/dashboard');

        } catch (\Exception $e) {
            DB::rollBack();
            $this->addError('email', 'Gagal mendaftar: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.auth.register');
    }
}