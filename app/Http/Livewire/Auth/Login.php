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
        if (auth()->check()) {
            return redirect()->route('dashboard');
        }
    }

    public function login()
    {
        $this->validate();

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

        session()->regenerate();
        return redirect()->intended('/dashboard');
    }

    public function render()
    {
        // PENTING: Gunakan layout guest-auth yang baru dibuat
        return view('livewire.auth.login')->layout('layouts.guest-auth');
    }
}