<?php

use App\Http\Controllers\TableSessionController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Table Session / QR Ordering Routes
|--------------------------------------------------------------------------
*/

// QR Code Scan - Public route (no auth required)
Route::get('/table/{qr_code}', [TableSessionController::class, 'scan'])
    ->name('table.scan')
    ->where('qr_code', '[A-Za-z0-9\-]+');

// Alternative short URL
Route::get('/t/{qr_code}', [TableSessionController::class, 'scan'])
    ->name('table.scan.short')
    ->where('qr_code', '[A-Za-z0-9\-]+');

// Menu page - Requires active table session
Route::get('/menu', [TableSessionController::class, 'menu'])
    ->name('table.menu')
    ->middleware('table.session');

// API routes for table session
Route::prefix('api/table')->middleware('table.session')->group(function () {
    // Get session info
    Route::get('/session', [TableSessionController::class, 'sessionInfo'])
        ->name('api.table.session');
    
    // Update guest count
    Route::post('/guests', [TableSessionController::class, 'updateGuests'])
        ->name('api.table.guests');
    
    // Call waiter
    Route::post('/call-waiter', [TableSessionController::class, 'callWaiter'])
        ->name('api.table.call-waiter');
    
    // Request bill
    Route::post('/request-bill', [TableSessionController::class, 'requestBill'])
        ->name('api.table.request-bill');
    
    // End session
    Route::post('/end-session', [TableSessionController::class, 'endSession'])
        ->name('api.table.end-session');
});


/*
|--------------------------------------------------------------------------
| Register Middleware in app/Http/Kernel.php
|--------------------------------------------------------------------------
|
| Add to $middlewareAliases array:
|
| 'table.session' => \App\Http\Middleware\TableSessionMiddleware::class,
|
*/
