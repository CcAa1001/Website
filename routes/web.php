<?php
use Illuminate\Support\Facades\Route;
use App\Http\Livewire\Dashboard;
use App\Http\Livewire\InventoryManager;
use App\Http\Livewire\PosSystem;
use App\Http\Livewire\CustomerManager;
use App\Http\Livewire\LaporanManager;
use App\Http\Livewire\Auth\Login;
use App\Http\Livewire\Auth\Register;

use App\Http\Livewire\PublicMenu;

Route::get('/menu/{userId}', \App\Http\Livewire\PublicMenu::class)->name('public.menu');


Route::get('/order/{tableId?}', PublicMenu::class)->name('public.menu');


Route::get('login', Login::class)->name('login');
Route::get('register', Register::class)->name('register');
Route::get('forgot-password', \App\Http\Livewire\Auth\ForgotPassword::class)->name('password.forgot');

Route::middleware('auth')->group(function () {
    Route::get('/', Dashboard::class)->name('dashboard');
    Route::get('dashboard', Dashboard::class);
    Route::get('inventory', InventoryManager::class)->name('inventory');
    Route::get('customers', CustomerManager::class)->name('customers');
    Route::get('reports', LaporanManager::class)->name('reports');
    Route::get('pos', PosSystem::class)->name('pos');
    Route::get('forgot-password', \App\Http\Livewire\Auth\ForgotPassword::class)->name('password.forgot');
});




Route::get('/', function () { 
    return redirect()->route('public.menu'); 
});

// Admin Protected Routes
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', Dashboard::class)->name('dashboard');
    // Ensure other admin routes like user-management or profile are kept here
});

// Login route
Route::get('/login', Login::class)->name('login');