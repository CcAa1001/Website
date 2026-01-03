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
use App\Http\Livewire\ExampleLaravel\UserManagement;
use App\Http\Livewire\ExampleLaravel\UserProfile;


use App\Http\Livewire\CategoryManager;


use App\Http\Livewire\Auth\ForgotPassword;




// Public Menu (QR Code Access)
Route::get('/order/{tableId?}', PublicMenu::class)->name('public.menu');
// Authentication Routes
Route::get('/order/{tableNumber}', PublicMenu::class)->name('public.menu');

Route::get('login', Login::class)->name('login');
Route::get('register', Register::class)->name('register');

// FIX: Define the missing forgot password route here
Route::get('forgot-password', ForgotPassword::class)->name('password.forgot');

// Admin Protected Routes
Route::middleware('auth')->group(function () {
    Route::get('/', function () { return redirect()->route('dashboard'); });
    Route::get('dashboard', Dashboard::class)->name('dashboard');
    
    // Inventory & POS
    Route::get('inventory', InventoryManager::class)->name('inventory');
    Route::get('pos', PosSystem::class)->name('pos');
    
    // Data Master (Example: Categories)
    // Note: You may need to create a CategoryManager component later
    Route::get('categories', InventoryManager::class)->name('categories'); 
    Route::get('customers', CustomerManager::class)->name('customers');
    
    // Reports
    Route::get('reports', LaporanManager::class)->name('reports');
    
    // Account Management
    Route::get('user-management', UserManagement::class)->name('user-management');
    Route::get('user-profile', UserProfile::class)->name('user-profile');




    Route::get('categories', CategoryManager::class)->name('categories');

});

