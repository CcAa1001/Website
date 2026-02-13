<?php
/**
 * Payment Route Diagnostic Script
 * Save this as: diagnose_payment.php in your Laravel root
 * Run with: php diagnose_payment.php
 */

echo "==========================================================\n";
echo "  PAYMENT ROUTE DIAGNOSTIC\n";
echo "==========================================================\n\n";

// Bootstrap Laravel
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$errors = [];
$warnings = [];

// CHECK 1: Controller File
echo "📁 CHECK 1: PaymentRedirectController\n";
echo str_repeat("-", 60) . "\n";

$controllerPath = app_path('Http/Controllers/PaymentRedirectController.php');
if (file_exists($controllerPath)) {
    echo "✅ Controller file exists\n";
    echo "   Path: {$controllerPath}\n";
    
    // Check if class is defined
    if (class_exists('App\Http\Controllers\PaymentRedirectController')) {
        echo "✅ Controller class loaded\n";
        
        // Check if redirect method exists
        $controller = new App\Http\Controllers\PaymentRedirectController();
        if (method_exists($controller, 'redirect')) {
            echo "✅ redirect() method exists\n";
        } else {
            $errors[] = "❌ redirect() method NOT found in controller!";
            echo "❌ redirect() method missing!\n";
        }
    } else {
        $errors[] = "❌ Controller class not autoloaded!";
        echo "❌ Controller class not found!\n";
        echo "   Run: composer dump-autoload\n";
    }
} else {
    $errors[] = "❌ PaymentRedirectController.php file does NOT exist!";
    echo "❌ Controller file NOT found!\n";
    echo "   Expected: {$controllerPath}\n";
}

echo "\n";

// CHECK 2: View File
echo "📄 CHECK 2: Payment Redirect View\n";
echo str_repeat("-", 60) . "\n";

$viewPath = resource_path('views/payment/redirect.blade.php');
if (file_exists($viewPath)) {
    echo "✅ View file exists\n";
    echo "   Path: {$viewPath}\n";
} else {
    $errors[] = "❌ View file does NOT exist!";
    echo "❌ View file NOT found!\n";
    echo "   Expected: {$viewPath}\n";
    echo "   Create directory: mkdir -p " . dirname($viewPath) . "\n";
}

echo "\n";

// CHECK 3: Route Registration
echo "🛣️  CHECK 3: Route Registration\n";
echo str_repeat("-", 60) . "\n";

try {
    $route = Route::getRoutes()->getByName('payment.redirect');
    if ($route) {
        echo "✅ Route 'payment.redirect' is registered\n";
        echo "   URI: " . $route->uri() . "\n";
        echo "   Methods: " . implode(', ', $route->methods()) . "\n";
        echo "   Action: " . $route->getActionName() . "\n";
        
        // Check middleware
        $middleware = $route->middleware();
        if (!empty($middleware)) {
            echo "   Middleware: " . implode(', ', $middleware) . "\n";
            
            // Check for auth middleware
            if (in_array('auth', $middleware)) {
                $warnings[] = "⚠️ Route has 'auth' middleware - requires login!";
                echo "   ⚠️  Route requires authentication!\n";
            }
        } else {
            echo "   Middleware: none\n";
        }
    } else {
        $errors[] = "❌ Route 'payment.redirect' NOT registered!";
        echo "❌ Route NOT found!\n";
        echo "   Check routes/web.php\n";
    }
} catch (\Exception $e) {
    $errors[] = "❌ Error checking route: " . $e->getMessage();
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\n";

// CHECK 4: Route Helper Test
echo "🔗 CHECK 4: Route Helper\n";
echo str_repeat("-", 60) . "\n";

try {
    $url = route('payment.redirect', ['order_id' => 'test', 'payment_id' => 'test']);
    echo "✅ Route helper works\n";
    echo "   Generated URL: {$url}\n";
    
    // Check if URL matches APP_URL
    $appUrl = config('app.url');
    echo "   APP_URL: {$appUrl}\n";
    
    if (strpos($url, 'localhost') !== false || strpos($url, '127.0.0.1') !== false) {
        echo "   ✅ URL uses localhost\n";
    } elseif (strpos($url, 'ngrok') !== false) {
        echo "   ⚠️  URL uses ngrok\n";
    }
} catch (\Exception $e) {
    $errors[] = "❌ Route helper error: " . $e->getMessage();
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\n";

// CHECK 5: Test Controller Instantiation
echo "🎛️  CHECK 5: Controller Test\n";
echo str_repeat("-", 60) . "\n";

try {
    if (class_exists('App\Http\Controllers\PaymentRedirectController')) {
        $controller = app()->make('App\Http\Controllers\PaymentRedirectController');
        echo "✅ Controller can be instantiated\n";
    } else {
        echo "❌ Controller class not found\n";
    }
} catch (\Exception $e) {
    $errors[] = "❌ Controller instantiation error: " . $e->getMessage();
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\n";

// CHECK 6: Payment Model
echo "💳 CHECK 6: Payment Model\n";
echo str_repeat("-", 60) . "\n";

try {
    $testPayment = App\Models\Payment::first();
    if ($testPayment) {
        echo "✅ Can query Payment model\n";
        echo "   Payment exists: {$testPayment->payment_number}\n";
        
        // Check relationships
        if ($testPayment->order) {
            echo "   ✅ Payment->order relationship works\n";
        } else {
            echo "   ⚠️  Payment has no order\n";
        }
        
        if ($testPayment->paymentMethod) {
            echo "   ✅ Payment->paymentMethod relationship works\n";
        } else {
            echo "   ⚠️  Payment has no payment method\n";
        }
        
        if ($testPayment->payment_url) {
            echo "   ✅ Payment has payment_url: " . substr($testPayment->payment_url, 0, 50) . "...\n";
        } else {
            echo "   ⚠️  Payment has no payment_url\n";
        }
    } else {
        echo "⚠️  No payments in database yet\n";
    }
} catch (\Exception $e) {
    $errors[] = "❌ Payment model error: " . $e->getMessage();
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\n";

// SUMMARY
echo "==========================================================\n";
echo "  SUMMARY\n";
echo "==========================================================\n\n";

if (empty($errors)) {
    echo "🎉 NO ERRORS FOUND!\n\n";
    echo "The route should work. Try accessing:\n";
    echo route('payment.redirect', ['order_id' => 'test', 'payment_id' => 'test']) . "\n\n";
    echo "If you still get 404:\n";
    echo "1. Clear cache: php artisan optimize:clear\n";
    echo "2. Restart server: php artisan serve\n";
    echo "3. Check .htaccess or web server config\n";
} else {
    echo "❌ ERRORS FOUND (" . count($errors) . "):\n\n";
    foreach ($errors as $i => $error) {
        echo "  " . ($i + 1) . ". " . $error . "\n";
    }
    echo "\n";
}

if (!empty($warnings)) {
    echo "⚠️  WARNINGS (" . count($warnings) . "):\n\n";
    foreach ($warnings as $i => $warning) {
        echo "  " . ($i + 1) . ". " . $warning . "\n";
    }
    echo "\n";
}

echo "==========================================================\n";