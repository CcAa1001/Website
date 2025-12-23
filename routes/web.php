<?php

use Illuminate\Support\Facades\Route;
use App\Http\Livewire\Dashboard;
use App\Http\Livewire\InventoryManager;
use App\Http\Livewire\PosSystem;
use App\Http\Livewire\Auth\Login;
use App\Http\Livewire\Auth\Register;
use App\Http\Livewire\Auth\ForgotPassword;
use App\Http\Livewire\Auth\ResetPassword;

// Public Route (For QR Code Scanning)
Route::get('/menu/{userId}', \App\Http\Livewire\PublicMenu::class)->name('public.menu');

// Auth Routes
Route::get('login', Login::class)->name('login');
Route::get('register', Register::class)->name('register');
Route::get('forgot-password', ForgotPassword::class)->name('password.forgot'); // FIXED
Route::get('reset-password', ResetPassword::class)->name('password.reset');

Route::middleware('auth')->group(function () {
    Route::get('/', Dashboard::class)->name('dashboard');
    Route::get('dashboard', Dashboard::class);
    
    // Startup Modules
    Route::get('inventory', InventoryManager::class)->name('inventory');
    Route::get('pos', PosSystem::class)->name('pos');
    
    // Profile & Management
    Route::get('profile', \App\Http\Livewire\ExampleLaravel\UserProfile::class)->name('user-profile');
    Route::get('user-management', \App\Http\Livewire\ExampleLaravel\UserManagement::class)->name('user-management');
    Route::get('billing', \App\Http\Livewire\Billing::class)->name('billing');
});