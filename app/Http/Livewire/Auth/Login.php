<?php

namespace App\Http\Livewire\Auth;

use Livewire\Component;
use App\Models\User;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class Login extends Component
{
    public $email = '';
    public $password = '';
    public $remember = false;

    protected $rules = [
        'email' => 'required|email',
        'password' => 'required',
    ];

    public function mount() {
        if(auth()->user()){
            redirect('/dashboard');
        }
    }

    public function login()
    {
        $this->validate();

        // 1. Anti-Brute Force Protection
        $throttleKey = Str::lower($this->email) . '|' . request()->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            $this->addError('email', "Terlalu banyak percobaan login. Silakan coba lagi dalam $seconds detik.");
            return;
        }

        // 2. Attempt Login
        if (auth()->attempt(['email' => $this->email, 'password' => $this->password], $this->remember)) {
            
            // Login Berhasil - Clear Rate Limiter
            RateLimiter::clear($throttleKey);
            
            $user = auth()->user();

            // 3. Security Check: Pastikan Akun Aktif
            if (!$user->is_active) {
                auth()->logout();
                $this->addError('email', 'Akun Anda dinonaktifkan. Hubungi Administrator.');
                return;
            }

            // 4. Role Check (Opsional: Jika ingin membatasi akses login hanya untuk staff tertentu)
            // if (!in_array($user->role->name, ['admin', 'manager', 'cashier'])) {
            //     auth()->logout();
            //     $this->addError('email', 'Anda tidak memiliki akses ke sistem ini.');
            //     return;
            // }

            return redirect()->intended('/dashboard');
        } else {
            // Login Gagal - Hitung Percobaan
            RateLimiter::hit($throttleKey, 60); // Blokir 60 detik setelah 5x gagal
            $this->addError('email', trans('auth.failed'));
        }
    }

    public function render()
    {
        return view('livewire.auth.login');
    }
}