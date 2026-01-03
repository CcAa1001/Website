<?php

use Illuminate\Support\Facades\Route;
use App\Http\Livewire\Dashboard;
use App\Http\Livewire\InventoryManager;
use App\Http\Livewire\PosSystem;
use App\Http\Livewire\CustomerManager;
use App\Http\Livewire\LaporanManager;
use App\Http\Livewire\PublicMenu;
use App\Http\Livewire\Auth\Login;
use App\Http\Livewire\Auth\Register;
use App\Http\Livewire\Auth\ForgotPassword;
use App\Http\Livewire\CategoryManager;

// Menu Publik untuk QR
Route::get('/order/{tableNumber}', PublicMenu::class)->name('public.menu');

// Auth Routes
Route::get('login', Login::class)->name('login');
Route::get('register', Register::class)->name('register');
Route::get('forgot-password', ForgotPassword::class)->name('password.forgot');

// Dashboard & Admin
Route::middleware('auth')->group(function () {
    Route::get('/', function () { return redirect()->route('dashboard'); });
    Route::get('dashboard', Dashboard::class)->name('dashboard');
    Route::get('inventory', InventoryManager::class)->name('inventory');
    Route::get('pos', PosSystem::class)->name('pos');
    Route::get('categories', CategoryManager::class)->name('categories'); 
    Route::get('customers', CustomerManager::class)->name('customers');
    Route::get('reports', LaporanManager::class)->name('reports');
    
    // Management User
    Route::get('user-management', \App\Http\Livewire\ExampleLaravel\UserManagement::class)->name('user-management');
    Route::get('user-profile', \App\Http\Livewire\ExampleLaravel\UserProfile::class)->name('user-profile');
});