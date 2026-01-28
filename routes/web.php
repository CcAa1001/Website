<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;

// ==========================================
// FRONTEND CONTROLLERS
// ==========================================
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\TableSessionController;

// ==========================================
// BACKEND ADMIN COMPONENTS
// ==========================================
use App\Http\Livewire\Dashboard;
use App\Http\Livewire\ProductManager;
use App\Http\Livewire\PosSystem;
use App\Http\Livewire\CustomerManager;
use App\Http\Livewire\LaporanManager;
use App\Http\Livewire\Auth\Login;
use App\Http\Livewire\Auth\Register;
use App\Http\Livewire\Auth\ForgotPassword;
use App\Http\Livewire\CategoryManager;
use App\Http\Livewire\ModifierManager;
use App\Http\Livewire\TableManager;
use App\Http\Livewire\ExampleLaravel\UserManagement;
use App\Http\Livewire\ExampleLaravel\UserProfile; // [NEW] Profile Management
use App\Http\Livewire\OrderManager;
use App\Http\Livewire\KitchenDisplay;
use App\Http\Livewire\TransactionHistory;
use App\Http\Livewire\RefundHistory;
use App\Http\Livewire\InventoryManager; // [NEW] Inventory System
use App\Http\Livewire\StoreSettings;    // [NEW] Store Settings

// Model Import for QR Logic
use App\Models\Table;

// ==========================================
// IMAGE SERVICE (For Testing)
// ==========================================
use App\Services\ImageService;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

/*
|--------------------------------------------------------------------------
| PUBLIC ROUTES (Customer Facing - QR Ordering & Shop)
|--------------------------------------------------------------------------
*/

// --- 1. QR CODE SCAN LOGIC (SMART LINK) ---
// Route ini menangkap hasil scan QR Code
Route::get('/scan/{qr_code}', function ($qr_code) {
    // Cari meja berdasarkan kode unik
    $table = Table::where('qr_code', $qr_code)->first();

    if (!$table) {
        abort(404, 'QR Code tidak valid atau Meja tidak ditemukan.');
    }

    // Redirect ke Menu Digital Meja tersebut
    return redirect()->route('table.menu', ['table_number' => $table->table_number]);

})->name('table.scan');

// --- 2. TABLE SESSION ROUTES (Legacy Support) ---
Route::get('/table/{qr_code}', [TableSessionController::class, 'scan'])->name('table.scan.legacy')->where('qr_code', '[A-Za-z0-9\-]+');
Route::get('/t/{qr_code}', [TableSessionController::class, 'scan'])->name('table.scan.short')->where('qr_code', '[A-Za-z0-9\-]+');
Route::get('/menu', [TableSessionController::class, 'menu'])->name('table.menu'); // Halaman Menu Pelanggan

// API Table Session
Route::prefix('api/table')->group(function () {
    Route::get('/session', [TableSessionController::class, 'sessionInfo'])->name('api.table.session');
    Route::post('/guests', [TableSessionController::class, 'updateGuests'])->name('api.table.guests');
    Route::post('/call-waiter', [TableSessionController::class, 'callWaiter'])->name('api.table.call-waiter');
    Route::post('/request-bill', [TableSessionController::class, 'requestBill'])->name('api.table.request-bill');
    Route::post('/end-session', [TableSessionController::class, 'endSession'])->name('api.table.end-session');
});

// --- 3. HOME & SHOP ROUTES ---
Route::get('/', function (){ return redirect()->route('login'); }); // Default redirect to login

Route::prefix('shop')->name('shop.')->group(function () {
    Route::get('/', function () { return view('public.shop.index'); })->name('index');
    Route::get('/product/{slug}', [ShopController::class, 'show'])->name('show');
    Route::get('/search', function () { return view('public.shop.index'); })->name('search');
    Route::get('/category/{slug}', function ($slug) {
        return redirect()->route('public.shop.index', ['category' => $slug]);
    })->name('category');
});

// Other Public Pages
Route::get('/flash-deals', function () { return view('flash-deals'); })->name('flash-deals');
Route::get('/wishlist', function () { return view('wishlist'); })->name('wishlist');
Route::get('/track-order', function () { return view('track-order'); })->name('track-order');
Route::get('/about', function () { return view('pages.about'); })->name('about');
Route::get('/contact', function () { return view('pages.contact'); })->name('contact');

// Blog & Cart Routes
Route::prefix('blog')->name('blog.')->group(function () {
    Route::get('/', function () { return view('blog.index'); })->name('index');
    Route::get('/{slug}', function ($slug) { return view('blog.show', ['slug' => $slug]); })->name('show');
});

Route::prefix('cart')->name('cart.')->group(function () {
    Route::get('/', function () { return view('cart.index'); })->name('index');
    Route::post('/add', function () {})->name('add');
    Route::post('/update', function () {})->name('update');
    Route::post('/remove', function () {})->name('remove');
});

Route::prefix('checkout')->name('checkout.')->group(function () {
    Route::get('/', function () { return view('checkout.index'); })->name('index');
    Route::post('/process', function () {})->name('process');
});

// Customer Account (Protected)
Route::middleware(['auth'])->prefix('account')->name('account.')->group(function () {
    Route::get('/dashboard', function () { return view('account.dashboard'); })->name('dashboard');
    Route::get('/orders', function () { return view('account.orders'); })->name('orders');
    Route::get('/profile', function () { return view('account.profile'); })->name('profile');
    Route::get('/addresses', function () { return view('account.addresses'); })->name('addresses');
});

/*
|--------------------------------------------------------------------------
| AUTHENTICATION ROUTES
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
| ADMIN/STAFF ROUTES (Authenticated)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {
    
    // 1. Dashboard & Core
    Route::get('/dashboard', Dashboard::class)->name('dashboard');
    Route::get('/pos', PosSystem::class)->name('pos');
    
    // 2. Product Management (Catalog)
    Route::get('/products', ProductManager::class)
        ->middleware('role:manager,admin,super_admin') // Updated Roles
        ->name('products');
    
    Route::get('/categories', CategoryManager::class)
        ->middleware('role:manager,admin,super_admin')
        ->name('categories');
    
    Route::get('/modifiers', ModifierManager::class)
        ->middleware('role:manager,admin,super_admin')
        ->name('modifiers');

    // [NEW] Inventory Management
    Route::get('/inventory', InventoryManager::class)
        ->middleware('role:manager,admin,super_admin')
        ->name('inventory');
    
    // 3. Operations (Tables, Orders, Kitchen)
    Route::get('/tables', TableManager::class)->name('tables'); // QR Management here
    Route::get('/orders', OrderManager::class)->name('orders');
    Route::get('/kitchen', KitchenDisplay::class)->name('kitchen');
    
    // 4. Business (Customers, Reports)
    Route::get('/customers', CustomerManager::class)->name('customers');
    
    Route::get('/reports', LaporanManager::class)
        ->middleware('role:supervisor,manager,admin,super_admin')
        ->name('reports');
    
    Route::get('/transactions', TransactionHistory::class)
        ->middleware('role:supervisor,manager,admin,super_admin')
        ->name('transactions');
    
    Route::get('/refund-history', RefundHistory::class)
        ->middleware('role:supervisor,manager,admin,super_admin')
        ->name('refund-history');

    // 5. Admin Settings (User Management)
    Route::get('/user-management', UserManagement::class)
        ->middleware('role:admin,super_admin') // Hanya Admin
        ->name('user-management');
        
    Route::get('/store-settings', StoreSettings::class)
        ->middleware('role:admin,super_admin')
        ->name('store-settings');

    Route::get('/profile', UserProfile::class)->name('profile');
});

/*
|--------------------------------------------------------------------------
| DEBUG & TEST ROUTES (Remove in Production)
|--------------------------------------------------------------------------
*/
Route::get('/debug-tables', function () {
    // ... (Kode debug tetap sama, berguna untuk testing)
    return 'Debug Mode Active';
});

Route::get('/test-image', function () {
    $manager = new ImageManager(new Driver());
    dump('✅ ImageManager created');
    $placeholder = \App\Models\ImageSetting::placeholderUrl();
    dump('✅ Placeholder: ' . $placeholder);
    return 'Image Test Passed';
});

Route::get('/test-payment', function() {
    $user = auth()->user();
    if(!$user) return "Login first";
    $methods = \App\Models\PaymentMethod::where('tenant_id', $user->tenant_id)->where('is_active', true)->get();
    return $methods;
})->middleware('auth');