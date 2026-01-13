<?php

namespace App\Http\Livewire\Auth;

use Livewire\Component;
use App\Models\User;

class Login extends Component
{
    public $email = '';
    public $password = '';
    public $remember_me = false;

    protected $rules = [
        'email' => 'required|email:rfc,dns',
        'password' => 'required|min:6',
    ];

    public function mount() 
    {
        // FIX: Use auth()->check() instead of auth()->user()
        //if (auth()->check()) {
           // return redirect()->intended('/dashboard');
        //}
    }

    public function login()  // CHANGED FROM 'store' to 'login'
    {
        $attributes = $this->validate();

        if (!auth()->attempt(['email' => $this->email, 'password' => $this->password], $this->remember_me)) {
            $this->addError('email', trans('auth.failed'));
            return;
        }

        $user = auth()->user();

        // Validasi Status Aktif
        if (!$user->is_active) {
            auth()->logout();
            $this->addError('email', 'Akun Anda dinonaktifkan.');
            return;
        }

        // Validasi Tenant Aktif
        if ($user->tenant_id) {
            $tenant = \App\Models\Tenant::find($user->tenant_id);
            if ($tenant && !$tenant->is_active) {
                auth()->logout();
                $this->addError('email', 'Langganan restoran ini sudah tidak aktif.');
                return;
            }
        }

        session()->regenerate();
        return redirect()->intended('/dashboard');
    }

    public function render()
    {
        return view('livewire.auth.login');
    }
}