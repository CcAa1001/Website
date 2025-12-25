<?php

use Illuminate\Support\Facades\Route;
use App\Http\Livewire\Dashboard;
use App\Http\Livewire\InventoryManager;
use App\Http\Livewire\PosSystem;
use App\Http\Livewire\PublicMenu;
use App\Http\Livewire\Auth\Login;
use App\Http\Livewire\Auth\Register;
use App\Http\Livewire\Auth\ForgotPassword;
use App\Http\Livewire\Auth\ResetPassword;

Route::get('/menu/{userId}', PublicMenu::class)->name('public.menu');

Route::get('login', Login::class)->name('login');
Route::get('register', Register::class)->name('register');
Route::get('forgot-password', ForgotPassword::class)->name('password.forgot');
Route::get('reset-password/{token}', ResetPassword::class)->name('password.reset');

Route::middleware('auth')->group(function () {
    Route::get('/', Dashboard::class)->name('dashboard');
    Route::get('dashboard', Dashboard::class);
    
    Route::get('pos', PosSystem::class)->name('pos');
    
    Route::get('inventory', InventoryManager::class)->name('inventory');
    
    Route::get('billing', \App\Http\Livewire\Billing::class)->name('billing');
    Route::get('profile', \App\Http\Livewire\Profile::class)->name('profile');
    Route::get('user-profile', \App\Http\Livewire\ExampleLaravel\UserProfile::class)->name('user-profile');
    Route::get('user-management', \App\Http\Livewire\ExampleLaravel\UserManagement::class)->name('user-management');
    Route::get('notifications', \App\Http\Livewire\Notifications::class)->name('notifications');
    Route::get('virtual-reality', \App\Http\Livewire\VirtualReality::class)->name('virtual-reality');
    Route::get('rtl', \App\Http\Livewire\RTL::class)->name('rtl');
    Route::get('tables', \App\Http\Livewire\Tables::class)->name('tables');


    Route::middleware('auth')->group(function () {
    Route::get('dashboard', Dashboard::class)->name('dashboard');
    Route::get('inventory', InventoryManager::class)->name('inventory');
    Route::get('customers', CustomerManager::class)->name('customers'); 
    Route::get('reports', LaporanManager::class)->name('reports');    
    Route::get('pos', PosSystem::class)->name('pos');
});
});