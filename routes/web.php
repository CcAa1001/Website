<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use App\Models\Table; // [WAJIB] Import Model Table untuk Smart Scan

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
use App\Http\Livewire\OrderManager;
use App\Http\Livewire\KitchenDisplay;
use App\Http\Livewire\TransactionHistory;
use App\Http\Livewire\RefundHistory;

// [NEW] Fitur Tambahan Kita
use App\Http\Livewire\InventoryManager;           // Manajemen Bahan Baku
use App\Http\Livewire\StoreSettings;              // Pengaturan Toko
use App\Http\Livewire\ExampleLaravel\UserManagement; // Manajemen Karyawan & Hak Akses
use App\Http\Livewire\ExampleLaravel\UserProfile;    // Profil User

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

// ========================================
// [UPGRADE] SMART QR SCAN LOGIC
// ========================================
// Route ini menangkap scan QR Code (baik link Google maupun kode meja)
Route::get('/scan/{qr_code}', function ($qr_code) {
    // 1. Cari meja berdasarkan kode unik
    $table = Table::where('qr_code', $qr_code)->first();

    if (!$table) {
        abort(404, 'QR Code tidak valid atau Meja tidak ditemukan.');
    }

    // 2. Redirect ke Menu Digital Meja tersebut
    return redirect()->route('table.menu', ['table_number' => $table->table_number]);

})->name('table.scan');


// ========================================
// TABLE SESSION (Legacy Routes teman Anda)
// ========================================
Route::get('/table/{qr_code}', [TableSessionController::class, 'scan'])
    ->name('table.scan.legacy')
    ->where('qr_code', '[A-Za-z0-9\-]+');

Route::get('/t/{qr_code}', [TableSessionController::class, 'scan'])
    ->name('table.scan.short')
    ->where('qr_code', '[A-Za-z0-9\-]+');

Route::get('/menu', [TableSessionController::class, 'menu'])
    ->name('table.menu');

// Table Session API
Route::prefix('api/table')->group(function () {
    Route::get('/session', [TableSessionController::class, 'sessionInfo'])->name('api.table.session');
    Route::post('/guests', [TableSessionController::class, 'updateGuests'])->name('api.table.guests');
    Route::post('/call-waiter', [TableSessionController::class, 'callWaiter'])->name('api.table.call-waiter');
    Route::post('/request-bill', [TableSessionController::class, 'requestBill'])->name('api.table.request-bill');
    Route::post('/end-session', [TableSessionController::class, 'endSession'])->name('api.table.end-session');
});

// ========================================
// HOME & SHOP
// ========================================

Route::get('/', function (){
    return redirect()->route('login');
});

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

// Blog
Route::prefix('blog')->name('blog.')->group(function () {
    Route::get('/', function () { return view('blog.index'); })->name('index');
    Route::get('/{slug}', function ($slug) { return view('blog.show', ['slug' => $slug]); })->name('show');
});

// Cart & Checkout
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

// Customer Account
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
        ->middleware('role:manager,admin,super_admin') 
        ->name('products');
    
    Route::get('/categories', CategoryManager::class)
        ->middleware('role:manager,admin,super_admin')
        ->name('categories');
    
    Route::get('/modifiers', ModifierManager::class)
        ->middleware('role:manager,admin,super_admin')
        ->name('modifiers');

    // [NEW] Inventory Management (Bahan Baku)
    Route::get('/inventory', InventoryManager::class)
        ->middleware('role:manager,admin,super_admin')
        ->name('inventory');
    
    // 3. Operations (Tables, Orders, Kitchen)
    Route::get('/tables', TableManager::class)->name('tables'); // Smart QR Management ada di sini
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

    // 5. Admin Settings (User Management & Store)
    // [NEW] User Management dengan Permission Checklist
    Route::get('/user-management', UserManagement::class)
        ->middleware('role:admin,super_admin') 
        ->name('user-management');
        
    // [NEW] Store Settings
    Route::get('/store-settings', StoreSettings::class)
        ->middleware('role:admin,super_admin')
        ->name('store-settings');

    // [NEW] User Profile
    Route::get('/profile', UserProfile::class)->name('profile');
});

/*
|--------------------------------------------------------------------------
| DEBUG ROUTES (Remove in production)
|--------------------------------------------------------------------------
*/

Route::get('/debug-tables', function () {
    $tables = DB::table('tables')
        ->join('outlets', 'tables.outlet_id', '=', 'outlets.id')
        ->select('tables.*', 'outlets.name as outlet_name', 'outlets.code as outlet_code', 'outlets.is_active as outlet_active')
        ->get();
    
    echo "<h2>All Tables in Database</h2>";
    echo "<pre>";
    foreach ($tables as $table) {
        echo "ID: {$table->id}\n";
        echo "Table Number: {$table->table_number}\n";
        echo "QR Code: " . ($table->qr_code ?? 'NULL') . "\n";
        echo "Is Active: " . ($table->is_active ? 'Yes' : 'No') . "\n";
        echo "Outlet: {$table->outlet_name} ({$table->outlet_code})\n";
        echo "Outlet Active: " . ($table->outlet_active ? 'Yes' : 'No') . "\n";
        echo "---\n";
    }
    echo "</pre>";
    
    $qrCode = request('qr', 'QR-JKT-01-A1');
    echo "<h2>Looking for QR Code: {$qrCode}</h2>";
    
    $found = DB::table('tables')->where('qr_code', $qrCode)->first();
    
    if ($found) {
        echo "<pre>Found: " . json_encode($found, JSON_PRETTY_PRINT) . "</pre>";
    } else {
        echo "<p style='color:red'>Not found!</p>";
    }
    
    return '';
});

Route::get('/test-image', function () {
    $manager = new ImageManager(new Driver());
    dump('✅ ImageManager created');
    
    $placeholder = \App\Models\ImageSetting::placeholderUrl();
    dump('✅ Placeholder URL: ' . $placeholder);
    
    $product = \App\Models\Product::with('images')->first();
    dump('✅ Product loaded with images');
    
    return 'All tests passed!';
});

Route::get('/test-component', function () {
    $product = \App\Models\Product::with('images', 'primaryImage')->first();
    return view('test-component', ['product' => $product]);
});

Route::get('/test-payment', function() {
    $user = auth()->user();
    if (!$user) return redirect('login');
    
    $methods = \App\Models\PaymentMethod::where('tenant_id', $user->tenant_id)
        ->where('is_active', true)
        ->get();
    
    if ($methods->isEmpty()) {
        return 'NO PAYMENT METHODS! Run the tinker commands.';
    }
    
    return $methods;
})->middleware('auth');