<?php

namespace App\Http\Livewire\Auth;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class Login extends Component
{
    public $email = '';
    public $password = '';
    public $remember = false;

    protected $rules = [
        'email' => 'required|email',
        'password' => 'required',
    ];

    // FIX: Mengubah nama fungsi dari 'login' ke 'store' sesuai wire:submit di view
    public function store()
    {
        $credentials = $this->validate();

        if (Auth::attempt(['email' => $this->email, 'password' => $this->password], $this->remember)) {
            session()->regenerate();
            return redirect()->intended('/dashboard');
        }

        $this->addError('email', 'Kombinasi email dan password tidak ditemukan.');
    }

    public function render()
    {
        return view('livewire.auth.login')->layout('layouts.base');
    }
}