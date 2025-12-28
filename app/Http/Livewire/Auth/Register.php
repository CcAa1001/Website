<?php

namespace App\Http\Livewire\Auth;

use App\Models\User;
use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class Register extends Component
{
    public $name = '';
    public $email = '';
    public $password = '';

    protected $rules = [
        'name' => 'required|min:3',
        'email' => 'required|email|unique:users,email',
        'password' => 'required|min:5',
    ];

    public function store()
    {
        $attributes = $this->validate();

        $user = DB::transaction(function () use ($attributes) {
            // 1. Buat Tenant Default
            $tenantId = (string) Str::uuid();
            DB::table('tenants')->insert([
                'id' => $tenantId,
                'code' => 'T-' . strtoupper(Str::random(5)),
                'name' => $attributes['name'] . ' Business',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // 2. Buat Outlet Default
            $outletId = (string) Str::uuid();
            DB::table('outlets')->insert([
                'id' => $outletId,
                'tenant_id' => $tenantId,
                'code' => 'OUT-001',
                'name' => 'Main Branch',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // 3. Buat User dan hubungkan ke Tenant & Outlet
            return User::create([
                'name' => $attributes['name'],
                'email' => $attributes['email'],
                'password' => $attributes['password'],
                'tenant_id' => $tenantId,
                'outlet_id' => $outletId,
            ]);
        });

        auth()->login($user);
        
        return redirect('/dashboard');
    }

    public function render()
    {
        return view('livewire.auth.register');
    }
}