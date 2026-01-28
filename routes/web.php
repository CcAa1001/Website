<?php

use Illuminate\Support\Facades\Route;
use App\Models\Table;
use App\Http\Controllers\TableSessionController;
use App\Http\Livewire\Dashboard;
use App\Http\Livewire\ProductManager;
use App\Http\Livewire\CategoryManager;
use App\Http\Livewire\ModifierManager;
use App\Http\Livewire\TableManager;
use App\Http\Livewire\OrderManager;
use App\Http\Livewire\KitchenDisplay;
use App\Http\Livewire\CustomerManager;
use App\Http\Livewire\LaporanManager;
use App\Http\Livewire\TransactionHistory;
use App\Http\Livewire\RefundHistory;
use App\Http\Livewire\InventoryManager;
use App\Http\Livewire\StoreSettings;
use App\Http\Livewire\UserManagement;
use App\Http\Livewire\ExampleLaravel\UserProfile;
use App\Http\Livewire\Auth\Login;
use App\Http\Livewire\Auth\Register;
use App\Http\Livewire\Auth\ForgotPassword;
use App\Http\Livewire\PosSystem;

/*
|--------------------------------------------------------------------------
| PUBLIC ROUTES (Customer Facing)
|--------------------------------------------------------------------------
*/

// [FIX] LOGIC LOGIN MEJA (Redirector)
// URL: http://127.0.0.1:8000/table/QR-JKT-01-5
Route::get('/table/{code}', function ($code) {
    // 1. Cari Meja berdasarkan QR Code (prioritas) atau ID
    $table = Table::where('qr_code', $code)
        ->orWhere('id', $code)
        ->first();

    if (!$table) {
        abort(404, 'Meja tidak ditemukan atau kode salah.');
    }

    // 2. Simpan Sesi Meja
    session()->put('table_id', $table->id);
    session()->put('outlet_id', $table->outlet_id);
    session()->put('tenant_id', $table->tenant_id); // Penting untuk filter produk

    // 3. Redirect ke Menu Utama
    return redirect()->route('table.menu');

})->name('table.login');

// Halaman Menu Utama (Yang Anda suka)
Route::get('/menu', [TableSessionController::class, 'menu'])->name('table.menu');

// Route Scan Alternatif
Route::get('/scan/{code}', function ($code) {
    return redirect()->route('table.login', ['code' => $code]);
});

// Home & Shop (Opsional)
Route::get('/', function (){ return redirect()->route('login'); });

/*
|--------------------------------------------------------------------------
| AUTH ROUTES
|--------------------------------------------------------------------------
*/
Route::get('login', Login::class)->name('login');
Route::get('register', Register::class)->name('register');
Route::get('forgot-password', ForgotPassword::class)->name('password.forgot');
Route::post('logout', function () {
    auth()->logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect()->route('login');
})->name('logout');

/*
|--------------------------------------------------------------------------
| ADMIN ROUTES
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', Dashboard::class)->name('dashboard');
    Route::get('/pos', PosSystem::class)->name('pos');
    
    // Management
    Route::get('/products', ProductManager::class)->middleware('role:manager,admin,super_admin')->name('products');
    Route::get('/categories', CategoryManager::class)->middleware('role:manager,admin,super_admin')->name('categories');
    Route::get('/modifiers', ModifierManager::class)->middleware('role:manager,admin,super_admin')->name('modifiers');
    Route::get('/inventory', InventoryManager::class)->middleware('role:manager,admin,super_admin')->name('inventory');
    
    // Operations
    Route::get('/tables', TableManager::class)->name('tables');
    Route::get('/orders', OrderManager::class)->name('orders');
    Route::get('/kitchen', KitchenDisplay::class)->name('kitchen');
    Route::get('/customers', CustomerManager::class)->name('customers');
    
    // Reports
    Route::get('/reports', LaporanManager::class)->middleware('role:supervisor,manager,admin,super_admin')->name('reports');
    Route::get('/transactions', TransactionHistory::class)->middleware('role:supervisor,manager,admin,super_admin')->name('transactions');
    Route::get('/refund-history', RefundHistory::class)->middleware('role:supervisor,manager,admin,super_admin')->name('refund-history');

    // Settings
    Route::get('/user-management', UserManagement::class)->middleware('role:admin,super_admin')->name('user-management');
    Route::get('/store-settings', StoreSettings::class)->middleware('role:admin,super_admin')->name('store-settings');
    Route::get('/profile', UserProfile::class)->name('profile');
});