<?php
use Illuminate\Support\Facades\Route;
use App\Http\Livewire\Dashboard;
use App\Http\Livewire\InventoryManager;
use App\Http\Livewire\PosSystem;
use App\Http\Livewire\CustomerManager;
use App\Http\Livewire\LaporanManager;
use App\Http\Livewire\Auth\Login;
use App\Http\Livewire\Auth\Register;

Route::get('/menu/{userId}', \App\Http\Livewire\PublicMenu::class)->name('public.menu');

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

    Route::get('profile', \App\Http\Livewire\Profile::class)->name('profile');
    Route::get('user-profile', \App\Http\Livewire\ExampleLaravel\UserProfile::class)->name('user-profile');
    Route::get('virtual-reality', \App\Http\Livewire\VirtualReality::class)->name('virtual-reality');
    Route::get('billing', \App\Http\Livewire\Billing::class)->name('billing');
    Route::get('notifications', \App\Http\Livewire\Notifications::class)->name('notifications');
    Route::get('rtl', \App\Http\Livewire\RTL::class)->name('rtl');
    Route::get('tables', \App\Http\Livewire\Tables::class)->name('tables');
});