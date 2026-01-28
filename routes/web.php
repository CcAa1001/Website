<?php

use Illuminate\Support\Facades\Route;

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
use App\Http\Livewire\UserManager;
use App\Http\Livewire\OrderManager;
use App\Http\Livewire\KitchenDisplay;
use App\Http\Livewire\TransactionHistory;
use App\Http\Livewire\RefundHistory;
// ==========================================
// FRONTEND PUBLIC LIVEWIRE (if exists)
// ==========================================
use App\Http\Livewire\Public\ShopProducts;

/*
|--------------------------------------------------------------------------
| PUBLIC ROUTES (Customer Facing - QR Ordering)
|--------------------------------------------------------------------------
*/

// ========================================
// TABLE SESSION / QR ORDERING
// ========================================

// QR Code Scan
Route::get('/table/{qr_code}', [TableSessionController::class, 'scan'])
    ->name('table.scan')
    ->where('qr_code', '[A-Za-z0-9\-]+');

// Short URL
Route::get('/t/{qr_code}', [TableSessionController::class, 'scan'])
    ->name('table.scan.short')
    ->where('qr_code', '[A-Za-z0-9\-]+');

// Menu page (requires session)
Route::get('/menu', [TableSessionController::class, 'menu'])
    ->name('table.menu');
    

// Table Session API
Route::prefix('api/table')->group(function () {
    Route::get('/session', [TableSessionController::class, 'sessionInfo'])
        ->name('api.table.session');
    
    Route::post('/guests', [TableSessionController::class, 'updateGuests'])
        ->name('api.table.guests');
    
    Route::post('/call-waiter', [TableSessionController::class, 'callWaiter'])
        ->name('api.table.call-waiter');
    
    Route::post('/request-bill', [TableSessionController::class, 'requestBill'])
        ->name('api.table.request-bill');
    
    Route::post('/end-session', [TableSessionController::class, 'endSession'])
        ->name('api.table.end-session');
});

// ========================================
// HOME & SHOP
// ========================================

// Home Page
Route::get('/', [HomeController::class, 'index'])->name('home');

// Shop Routes
Route::prefix('shop')->name('shop.')->group(function () {
    // Main shop page
    Route::get('/', function () {
        return view('public.shop.index');
    })->name('index');
    
    // Product detail page
    Route::get('/product/{slug}', [ShopController::class, 'show'])->name('show');
    
    // Search
    Route::get('/search', function () {
        return view('public.shop.index');
    })->name('search');
    
    // Category
    Route::get('/category/{slug}', function ($slug) {
        return redirect()->route('public.shop.index', ['category' => $slug]);
    })->name('category');
});

// ========================================
// OTHER PUBLIC PAGES
// ========================================

Route::get('/flash-deals', function () {
    return view('flash-deals');
})->name('flash-deals');

Route::get('/wishlist', function () {
    return view('wishlist');
})->name('wishlist');

Route::get('/track-order', function () {
    return view('track-order');
})->name('track-order');

Route::get('/about', function () {
    return view('pages.about');
})->name('about');

Route::get('/contact', function () {
    return view('pages.contact');
})->name('contact');

// Blog
Route::prefix('blog')->name('blog.')->group(function () {
    Route::get('/', function () {
        return view('blog.index');
    })->name('index');
    
    Route::get('/{slug}', function ($slug) {
        return view('blog.show', ['slug' => $slug]);
    })->name('show');
});

// Cart
Route::prefix('cart')->name('cart.')->group(function () {
    Route::get('/', function () {
        return view('cart.index');
    })->name('index');
    
    Route::post('/add', function () {
        // Add to cart logic
    })->name('add');
    
    Route::post('/update', function () {
        // Update cart logic
    })->name('update');
    
    Route::post('/remove', function () {
        // Remove from cart logic
    })->name('remove');
});

// Checkout
Route::prefix('checkout')->name('checkout.')->group(function () {
    Route::get('/', function () {
        return view('checkout.index');
    })->name('index');
    
    Route::post('/process', function () {
        // Process checkout logic
    })->name('process');
});

// Customer Account
Route::middleware(['auth'])->prefix('account')->name('account.')->group(function () {
    Route::get('/dashboard', function () {
        return view('account.dashboard');
    })->name('dashboard');
    
    Route::get('/orders', function () {
        return view('account.orders');
    })->name('orders');
    
    Route::get('/profile', function () {
        return view('account.profile');
    })->name('profile');
    
    Route::get('/addresses', function () {
        return view('account.addresses');
    })->name('addresses');
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
    
    // Dashboard & Core
    Route::get('/dashboard', Dashboard::class)->name('dashboard');
    Route::get('/pos', PosSystem::class)->name('pos');
    
    // Product Management
    Route::get('/products', ProductManager::class)
        ->middleware('role:manager,admin')
        ->name('products');
    Route::get('/categories', CategoryManager::class)
        ->middleware('role:manager,admin')
        ->name('categories');
    Route::get('/modifiers', ModifierManager::class)
        ->middleware('role:manager,admin')
        ->name('modifiers');
    
    // Operations
    Route::get('/tables', TableManager::class)->name('tables');
    Route::get('/orders', OrderManager::class)->name('orders');
    Route::get('/kitchen', KitchenDisplay::class)->name('kitchen');
    
    // Customers & Reports
    Route::get('/customers', CustomerManager::class)->name('customers');
    Route::get('/reports', LaporanManager::class)
        ->middleware('role:supervisor,manager,admin')
        ->name('reports');
    Route::get('/transactions', TransactionHistory::class)
        ->middleware('role:supervisor,manager,admin')
        ->name('transactions');
    Route::get('/refund-history', RefundHistory::class)
    ->middleware('role:supervisor,manager,admin')
    ->name('refund-history');
    
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
    
    echo "<h2>Table Sessions Table</h2>";
    try {
        $sessionCount = DB::table('table_sessions')->count();
        echo "<p>Table exists. Sessions: {$sessionCount}</p>";
    } catch (\Exception $e) {
        echo "<p style='color:red'>Table does not exist! Run: php artisan migrate</p>";
        echo "<pre>" . $e->getMessage() . "</pre>";
    }
    
    return '';
});
use App\Services\ImageService;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
Route::get('/test-image', function () {
    $manager = new ImageManager(new Driver());
    dump('✅ ImageManager created');
    
    // Test 2: Check settings
    $placeholder = \App\Models\ImageSetting::placeholderUrl();
    dump('✅ Placeholder URL: ' . $placeholder);
    
    // Test 3: Check models
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
    
    $methods = \App\Models\PaymentMethod::where('tenant_id', $user->tenant_id)
        ->where('is_active', true)
        ->get();
    
    if ($methods->isEmpty()) {
        return 'NO PAYMENT METHODS! Run the tinker commands.';
    }
    
    return $methods;
})->middleware('auth');