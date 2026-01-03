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

    public function mount() {
        if (auth()->user()) {
            return redirect()->intended('/dashboard');
        }
    }

    // UBAH DARI 'login' MENJADI 'store' AGAR SESUAI DENGAN TOMBOL HTML
    public function store()
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
        $tenant = \App\Models\Tenant::find($user->tenant_id);
        if ($tenant && !$tenant->is_active) {
            auth()->logout();
            $this->addError('email', 'Langganan restoran ini sudah tidak aktif.');
            return;
        }

        return redirect()->intended('/dashboard');
    }

    public function render()
    {
        return view('livewire.auth.login');
    }
}