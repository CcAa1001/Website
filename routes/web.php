<?php

use Illuminate\Support\Facades\Route;
use App\Http\Livewire\Dashboard;
use App\Http\Livewire\ProductManager;
use App\Http\Livewire\PosSystem;
use App\Http\Livewire\CustomerManager;
use App\Http\Livewire\LaporanManager;
use App\Http\Livewire\PublicMenu;
use App\Http\Livewire\Auth\Login;
use App\Http\Livewire\Auth\Register;
use App\Http\Livewire\Auth\ForgotPassword;
use App\Http\Livewire\CategoryManager;
use App\Http\Livewire\ModifierManager;
use App\Http\Livewire\TableManager;

// FIX: Point this to the correct existing file
use App\Http\Livewire\ExampleLaravel\UserManagement; 
use App\Http\Livewire\ExampleLaravel\UserProfile;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Redirect root to login
Route::get('/', function () {
    return redirect('login');
});

// Menu Publik untuk QR
Route::get('/order/{tableNumber}', PublicMenu::class)->name('public.menu');

// Auth Routes
Route::get('login', Login::class)->name('login');
Route::get('register', Register::class)->name('register');
Route::get('forgot-password', ForgotPassword::class)->name('password.forgot');

Route::middleware(['auth'])->group(function () {
    // Dashboard & Core
    Route::get('/dashboard', Dashboard::class)->name('dashboard');
    Route::get('/pos', PosSystem::class)->name('pos');
    
    // Product Management
    Route::get('/products', ProductManager::class)->name('products');
    Route::get('/categories', CategoryManager::class)->name('categories');
    Route::get('/modifiers', ModifierManager::class)->name('modifiers');
    
    // Operations
    Route::get('/tables', TableManager::class)->name('tables');
    
    // Reports & Customers
    Route::get('/customers', CustomerManager::class)->name('customers');
    Route::get('/reports', LaporanManager::class)->name('reports');

    // ADMIN SETTINGS (Fixed Routes)
    Route::get('/user-management', UserManagement::class)->name('user-management');
    Route::get('/user-profile', UserProfile::class)->name('user-profile');
});