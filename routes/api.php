<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

// Import all models
use App\Models\User;
use App\Models\Tenant;
use App\Models\Product;
use App\Models\Category;
use App\Models\Table;
use App\Models\PaymentMethod;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;

/*
|--------------------------------------------------------------------------
| API Routes for Flutter POS
|--------------------------------------------------------------------------
*/
// Image proxy route (serves storage images with CORS headers)
Route::get('/image/{path}', function ($path) {
    $filePath = storage_path('app/public/' . $path);
    
    if (!file_exists($filePath)) {
        abort(404);
    }
    
    $mimeType = mime_content_type($filePath);
    
    return response()->file($filePath, [
        'Access-Control-Allow-Origin' => '*',
        'Access-Control-Allow-Methods' => 'GET, OPTIONS',
        'Content-Type' => $mimeType,
        'Cache-Control' => 'public, max-age=86400',
    ]);
})->where('path', '.*');
/*
|--------------------------------------------------------------------------
| AUTHENTICATION API
|--------------------------------------------------------------------------
*/

Route::post('/login', function (Request $request) {
    $request->validate([
        'email' => 'required|email',
        'password' => 'required|min:6',
    ]);

    // Find user
    $user = User::where('email', $request->email)->first();

    // Check if user exists and password is correct
    if (!$user || !Hash::check($request->password, $user->password)) {
        return response()->json([
            'success' => false,
            'message' => 'Email atau password salah',
        ], 401);
    }

    // Check if user is active
    if (!$user->is_active) {
        return response()->json([
            'success' => false,
            'message' => 'Akun Anda dinonaktifkan',
        ], 403);
    }

    // Check if tenant is active
    if ($user->tenant_id) {
        $tenant = \App\Models\Tenant::find($user->tenant_id);
        if (!$tenant || !$tenant->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Langganan restoran ini sudah tidak aktif',
            ], 403);
        }
    }

    // Update last login
    $user->update(['last_login_at' => now()]);

    // Create token
    $token = $user->createToken('mobile-pos-' . $user->id)->plainTextToken;

    return response()->json([
        'success' => true,
        'message' => 'Login berhasil',
        'token' => $token,
        'user' => [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'role' => $user->role ? $user->role->name : null,
            'tenant_id' => $user->tenant_id,
            'outlet_id' => $user->outlet_id,
            'avatar_url' => $user->avatar_url,
            'employee_code' => $user->employee_code,
        ],
    ]);
});

Route::post('/logout', function (Request $request) {
    // Revoke current token
    $request->user()->currentAccessToken()->delete();

    return response()->json([
        'success' => true,
        'message' => 'Logout berhasil',
    ]);
})->middleware('auth:sanctum');

Route::get('/me', function (Request $request) {
    $user = $request->user();
    
    return response()->json([
        'success' => true,
        'user' => [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'role' => $user->role ? $user->role->name : null,
            'tenant_id' => $user->tenant_id,
            'outlet_id' => $user->outlet_id,
            'avatar_url' => $user->avatar_url,
            'employee_code' => $user->employee_code,
        ],
    ]);
})->middleware('auth:sanctum');

Route::post('/refresh-token', function (Request $request) {
    $user = $request->user();
    
    // Revoke old token
    $request->user()->currentAccessToken()->delete();
    
    // Create new token
    $token = $user->createToken('mobile-pos-' . $user->id)->plainTextToken;
    
    return response()->json([
        'success' => true,
        'token' => $token,
    ]);
})->middleware('auth:sanctum');



// ==========================================
// HELPER FUNCTIONS
// ==========================================

if (!function_exists('getTenantId')) {
    function getTenantId() {
        // Use authenticated user's tenant_id
        if (auth('sanctum')->check()) {
            $user = auth('sanctum')->user();
            if ($user && $user->tenant_id) {
                return $user->tenant_id;
            }
        }
        
        // Fallback for unauthenticated requests
        return \App\Models\Tenant::where('is_active', true)->first()->id ?? null;
    }
}

if (!function_exists('getUserId')) {
    function getUserId() {
        if (auth()->check()) {
            return auth()->id();
        }
        return \App\Models\User::first()->id ?? null;
    }
}

// ==========================================
// HEALTH CHECK
// ==========================================

Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'timestamp' => now()->toIso8601String(),
        'tenant_id' => getTenantId(),
    ]);
});

// ==========================================
// AUTHENTICATED ROUTES
// ==========================================

Route::middleware('auth:sanctum')->group(function () {

// ==========================================
// PRODUCTS API
// ==========================================

Route::get('/products', function (Request $request) {
    $tenantId = getTenantId();
    
    if (!$tenantId) {
        return response()->json(['error' => 'No tenant found'], 404);
    }
    
    $query = Product::where('tenant_id', $tenantId)
        ->where('is_available', true)
        ->with(['category', 'modifierGroups.modifiers', 'primaryImage']);
    
    if ($request->has('search') && $request->search) {
        $search = $request->search;
        $query->where(function($q) use ($search) {
            $q->where('name', 'ilike', '%' . $search . '%')
              ->orWhere('sku', 'ilike', '%' . $search . '%');
        });
    }
    
    if ($request->has('category_id') && $request->category_id) {
        $query->where('category_id', $request->category_id);
    }
    
    $products = $query->orderBy('sort_order')
        ->orderBy('name')
        ->get()
        ->map(function ($product) {

        
            // Build full image URL
            $imageUrl = null;
            if ($product->primaryImage) {
                $path = $product->primaryImage->medium_path 
                    ?? $product->primaryImage->original_path;
                
                if ($path) {
                    $imageUrl = url('api/image/' . $path);
                }
            }

            if (!$imageUrl) {
                $imageUrl = 'https://placehold.co/400x300/e2e8f0/64748b?text=' . urlencode($product->name);
            }
            // Fallback to medium_image accessor
            if (!$imageUrl && $product->medium_image) {
                $img = $product->medium_image;
                if (str_starts_with($img, 'http')) {
                    $imageUrl = $img;
                } else {
                    $imagePath = storage_path('app/public/' . ltrim($img, '/'));
                    if (file_exists($imagePath)) {
                        $imageUrl = url('storage/' . ltrim($img, '/'));
                    }
                }
            }
            
            // Final fallback: placeholder with product name
            if (!$imageUrl) {
                $imageUrl = 'https://placehold.co/400x300/e2e8f0/64748b?text=' . urlencode($product->name);
            }
            
            return [
                'id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'base_price' => (float) $product->base_price,
                'medium_image' => $imageUrl,
                'category_id' => $product->category_id,
                'modifier_groups' => $product->modifierGroups->map(function ($group) {
                    return [
                        'id' => $group->id,
                        'name' => $group->name,
                        'is_required' => (bool) $group->is_required,
                        'selection_type' => $group->selection_type ?? 'multiple',
                        'min_selections' => (int) ($group->min_selections ?? 0),
                        'max_selections' => $group->max_selections ? (int) $group->max_selections : null,
                        'modifiers' => $group->modifiers->map(function ($modifier) {
                            return [
                                'id' => $modifier->id,
                                'name' => $modifier->name,
                                'price' => (float) $modifier->price,
                            ];
                        }),
                    ];
                }),
            ];
        });
    
    return response()->json(['data' => $products]);
});

Route::get('/products/{id}', function ($id) {
    $tenantId = getTenantId();
    
    $product = Product::where('tenant_id', $tenantId)
        ->where('id', $id)
        ->with(['modifierGroups.modifiers', 'primaryImage'])
        ->first();
    
    if (!$product) {
        return response()->json(['error' => 'Product not found'], 404);
    }
    
    return response()->json([
        'id' => $product->id,
        'name' => $product->name,
        'sku' => $product->sku,
        'base_price' => (float) $product->base_price,
        'medium_image' => $product->medium_image ?? 'https://via.placeholder.com/300',
        'category_id' => $product->category_id,
        'modifier_groups' => $product->modifierGroups->map(function ($group) {
            return [
                'id' => $group->id,
                'name' => $group->name,
                'is_required' => (bool) $group->is_required,
                'selection_type' => $group->selection_type ?? 'multiple',
                'min_selections' => (int) ($group->min_selections ?? 0),
                'max_selections' => $group->max_selections ? (int) $group->max_selections : null,
                'modifiers' => $group->modifiers->map(function ($modifier) {
                    return [
                        'id' => $modifier->id,
                        'name' => $modifier->name,
                        'price' => (float) $modifier->price,
                    ];
                }),
            ];
        }),
    ]);
});

// ==========================================
// CATEGORIES API
// ==========================================

Route::get('/categories', function () {
    $tenantId = getTenantId();
    
    if (!$tenantId) {
        return response()->json(['error' => 'No tenant found'], 404);
    }
    
    $categories = Category::where('tenant_id', $tenantId)
        ->where('is_active', true)
        ->withCount(['products' => function($q) {
            $q->where('is_available', true);
        }])
        ->orderBy('sort_order')
        ->orderBy('name')
        ->get()
        ->map(function ($category) {
            return [
                'id' => $category->id,
                'name' => $category->name,
                'products_count' => $category->products_count,
            ];
        });
    
    return response()->json($categories);
});

// ==========================================
// TABLES API
// ==========================================

Route::get('/tables', function () {
    $tenantId = getTenantId();
    
    if (!$tenantId) {
        return response()->json(['error' => 'No tenant found'], 404);
    }
    
    $tables = Table::where('tenant_id', $tenantId)
        ->where('is_active', true)
        ->orderBy('table_number')
        ->get()
        ->map(function ($table) {
            return [
                'id' => $table->id,
                'table_number' => $table->table_number,
                'status' => $table->status ?? 'available',
                'status_label' => ucfirst($table->status ?? 'available'),
            ];
        });
    
    return response()->json($tables);
});

// ==========================================
// PAYMENT METHODS API
// ==========================================

Route::get('/payment-methods', function () {
    $tenantId = getTenantId();
    
    if (!$tenantId) {
        return response()->json(['error' => 'No tenant found'], 404);
    }
    
    $methods = PaymentMethod::where('tenant_id', $tenantId)
        ->where('is_active', true)
        ->orderBy('name')
        ->get()
        ->map(function ($method) {
            return [
                'id' => $method->id,
                'name' => $method->name,
                'payment_type' => strtolower($method->type ?? 'cash'),
            ];
        });
    
    return response()->json($methods);
});

// ==========================================
// ORDERS API
// ==========================================

Route::post('/orders', function (Request $request) {
    $tenantId = getTenantId();
    $userId = getUserId();
    
    if (!$tenantId || !$userId) {
        return response()->json([
            'success' => false,
            'message' => 'Authentication required',
        ], 401);
    }
    
    try {
        DB::beginTransaction();
        
        $orderNumber = 'ORD-' . date('Ymd') . '-' . strtoupper(substr(md5(time()), 0, 6));
        
        $order = Order::create([
            'tenant_id' => $tenantId,
            'outlet_id' => auth('sanctum')->user()->outlet_id,  // ← Also add outlet_id!
            'user_id' => $userId,
            'order_number' => $orderNumber,
            'order_type' => $request->order_type ?? 'dine_in',
            'order_source' => 'mobile_pos',
            'track_in_kitchen' => $request->track_in_kitchen ?? false,  // ← NEW
            'table_id' => $request->table_id,
            'guest_count' => $request->guest_count ?? 1,
            'customer_name' => $request->customer_name,
            'customer_phone' => $request->customer_phone,
            'subtotal' => $request->subtotal,
            'tax_amount' => $request->tax_amount,
            'service_charge' => $request->service_charge ?? 0,
            'discount_amount' => $request->discount_amount ?? 0,
            'grand_total' => $request->grand_total,
            'status' => 'pending',
            'payment_status' => 'unpaid',
        ]);
        
        foreach ($request->items as $item) {
            $orderItem = OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'base_price' => $item['base_price'],
                'modifiers' => json_encode($item['modifiers'] ?? []),
                'special_instructions' => $item['special_instructions'] ?? null,
            ]);
            
            $modifiersTotal = 0;
            if (isset($item['modifiers']) && is_array($item['modifiers'])) {
                foreach ($item['modifiers'] as $modifier) {
                    $modifiersTotal += $modifier['price'] ?? 0;
                }
            }
            
            $itemTotal = ($item['base_price'] + $modifiersTotal) * $item['quantity'];
            $orderItem->update(['total' => $itemTotal]);
        }
        
        foreach ($request->payments as $payment) {
            Payment::create([
                'tenant_id' => $tenantId,
                'order_id' => $order->id,
                'payment_method_id' => $payment['method_id'],
                'amount' => $payment['amount'],
                'cash_received' => $payment['cash_received'] ?? null,
                'status' => 'completed',
            ]);
        }
        
        if ($request->order_type === 'dine_in' && $request->table_id) {
            Table::where('id', $request->table_id)->update([
                'status' => 'occupied',
                'current_order_id' => $order->id,
            ]);
        }
        
        DB::commit();
        
        return response()->json([
            'success' => true,
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'message' => 'Order created successfully',
        ]);
        
    } catch (\Exception $e) {
        DB::rollBack();
        
        return response()->json([
            'success' => false,
            'message' => 'Failed to create order: ' . $e->getMessage(),
        ], 500);
    }
});
// ==========================================
// DASHBOARD API (for Flutter)
// ==========================================

Route::get('/dashboard/stats', function (Request $request) {
    $user = $request->user();
    $tenantId = $user->tenant_id;
    $outletId = $user->outlet_id;

    $todaysOrders = Order::where('tenant_id', $tenantId)
        ->where('outlet_id', $outletId)
        ->whereDate('created_at', today())
        ->get();

    $tableTotal = \App\Models\Table::where('outlet_id', $outletId)->count();
    $tableOccupied = \App\Models\Table::where('outlet_id', $outletId)->where('status', 'occupied')->count();

    return response()->json([
        'todays_earnings' => (float) $todaysOrders->whereIn('status', ['completed', 'served'])->sum('grand_total'),
        'total_orders' => $todaysOrders->count(),
        'active_orders_count' => Order::where('tenant_id', $tenantId)
            ->where('outlet_id', $outletId)
            ->whereIn('status', ['pending', 'confirmed', 'preparing', 'ready'])
            ->count(),
        'table_stats' => [
            'total' => $tableTotal,
            'occupied' => $tableOccupied,
        ],
    ]);
});

Route::get('/dashboard/stats', function (Request $request) {
    $user = $request->user();
    $tenantId = $user->tenant_id;
    $outletId = $user->outlet_id;

    $todaysOrders = Order::where('tenant_id', $tenantId)
        ->where('outlet_id', $outletId)
        ->whereDate('created_at', today())
        ->get();

    $tableTotal = \App\Models\Table::where('outlet_id', $outletId)->count();
    $tableOccupied = \App\Models\Table::where('outlet_id', $outletId)->where('status', 'occupied')->count();

    return response()->json([
        'todays_earnings' => (float) $todaysOrders->whereIn('status', ['completed', 'served'])->sum('grand_total'),
        'total_orders' => $todaysOrders->count(),
        'active_orders_count' => Order::where('tenant_id', $tenantId)
            ->where('outlet_id', $outletId)
            ->whereIn('status', ['pending', 'confirmed', 'preparing', 'ready'])
            ->trackedInKitchen()  // ← Only count tracked orders
            ->count(),
        'table_stats' => [
            'total' => $tableTotal,
            'occupied' => $tableOccupied,
        ],
    ]);
});

Route::get('/dashboard/orders', function (Request $request) {
    $user = $request->user();

    $orders = Order::where('tenant_id', $user->tenant_id)
        ->where('outlet_id', $user->outlet_id)
        ->whereIn('status', ['pending', 'confirmed', 'preparing', 'ready'])
        ->trackedInKitchen()  // ← Only show tracked orders on Kanban
        ->with(['table', 'items.product', 'customer'])
        ->orderBy('created_at', 'asc')
        ->get()
        ->map(function ($order) {
            return [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'status' => $order->status,
                'order_source' => $order->order_source,
                'table' => [
                    'table_number' => $order->table?->table_number,
                ],
                'guest_count' => $order->guest_count ?? 0,
                'grand_total' => (float) $order->grand_total,
                'notes' => $order->notes,
                'created_at' => $order->created_at->toIso8601String(),
                'items' => $order->items->map(function ($item) {
                    return [
                        'product_name' => $item->product_name ?? $item->product?->name ?? '',
                        'quantity' => $item->quantity,
                        'modifiers' => is_string($item->modifiers)
                            ? (json_decode($item->modifiers, true) ?? [])
                            : ($item->modifiers ?? []),
                        'notes' => $item->notes ?? $item->special_instructions ?? null,
                    ];
                }),
            ];
        })
        ->groupBy('status');

    return response()->json([
        'pending' => $orders->get('pending', collect())->values(),
        'confirmed' => $orders->get('confirmed', collect())->values(),
        'preparing' => $orders->get('preparing', collect())->values(),
        'ready' => $orders->get('ready', collect())->values(),
    ]);
});

Route::get('/dashboard/sessions', function (Request $request) {
    $user = $request->user();

    $sessions = \App\Models\TableSession::where('tenant_id', $user->tenant_id)
        ->where('outlet_id', $user->outlet_id)
        ->active()
        ->with(['table', 'orders'])
        ->get()
        ->map(function ($session) {
            return [
                'id' => $session->id,
                'table' => [
                    'table_number' => $session->table?->table_number,
                ],
                'started_at' => $session->started_at->toIso8601String(),
                'guest_count' => $session->guest_count,
                'order_count' => $session->orders->count(),
                'total_amount' => (float) $session->orders->sum('grand_total'),
                'status' => $session->status,
            ];
        });

    return response()->json(['data' => $sessions]);
});

Route::put('/orders/{id}/status', function (Request $request, $id) {
    $user = $request->user();
    $request->validate(['status' => 'required|in:pending,confirmed,preparing,ready,served,completed,cancelled']);

    $order = Order::where('id', $id)
        ->where('tenant_id', $user->tenant_id)
        ->firstOrFail();

    $oldStatus = $order->status;
    $newStatus = $request->status;

    $updates = ['status' => $newStatus];

    switch ($newStatus) {
        case 'confirmed': $updates['confirmed_at'] = now(); break;
        case 'preparing': $updates['confirmed_at'] = $order->confirmed_at ?? now(); break;
        case 'ready': $updates['prepared_at'] = now(); break;
        case 'served': $updates['prepared_at'] = $order->prepared_at ?? now(); break;
        case 'completed': $updates['completed_at'] = now(); break;
        case 'cancelled': $updates['cancelled_at'] = now(); break;
    }

    $order->update($updates);

    // Broadcast the status change
    broadcast(new \App\Events\OrderStatusChanged($order->fresh(), $oldStatus, $newStatus))->toOthers();

    return response()->json([
        'success' => true,
        'message' => 'Status updated',
        'order_id' => $order->id,
        'old_status' => $oldStatus,
        'new_status' => $newStatus,
    ]);
});

Route::get('/dashboard/sessions', function (Request $request) {
    $user = $request->user();

    $sessions = \App\Models\TableSession::where('tenant_id', $user->tenant_id)
        ->where('outlet_id', $user->outlet_id)
        ->active()
        ->with(['table', 'orders'])
        ->get()
        ->map(function ($session) {
            return [
                'id' => $session->id,
                'table' => [
                    'table_number' => $session->table?->table_number,
                ],
                'started_at' => $session->started_at->toIso8601String(),
                'guest_count' => $session->guest_count,
                'order_count' => $session->orders->count(),
                'total_amount' => (float) $session->orders->sum('grand_total'),
                'status' => $session->status,
            ];
        });

    return response()->json(['data' => $sessions]);
});

Route::put('/orders/{id}/status', function (Request $request, $id) {
    $user = $request->user();
    $request->validate(['status' => 'required|in:pending,confirmed,preparing,ready,served,completed,cancelled']);

    $order = Order::where('id', $id)
        ->where('tenant_id', $user->tenant_id)
        ->firstOrFail();

    $oldStatus = $order->status;
    $newStatus = $request->status;

    $updates = ['status' => $newStatus];

    switch ($newStatus) {
        case 'confirmed': $updates['confirmed_at'] = now(); break;
        case 'preparing': $updates['confirmed_at'] = $order->confirmed_at ?? now(); break;
        case 'ready': $updates['prepared_at'] = now(); break;
        case 'served': $updates['prepared_at'] = $order->prepared_at ?? now(); break;
        case 'completed': $updates['completed_at'] = now(); break;
        case 'cancelled': $updates['cancelled_at'] = now(); break;
    }

    $order->update($updates);

    // Broadcast the status change
    broadcast(new \App\Events\OrderStatusChanged($order->fresh(), $oldStatus, $newStatus))->toOthers();

    return response()->json([
        'success' => true,
        'message' => 'Status updated',
        'order_id' => $order->id,
        'old_status' => $oldStatus,
        'new_status' => $newStatus,
    ]);
});

// Pusher auth endpoint for Flutter (private channels)
Route::post('/broadcasting/auth', function (Request $request) {
    $user = $request->user();
    $pusher = new \Pusher\Pusher(
        config('broadcasting.connections.pusher.key'),
        config('broadcasting.connections.pusher.secret'),
        config('broadcasting.connections.pusher.app_id'),
        config('broadcasting.connections.pusher.options') ?? []
    );

    $channelName = $request->input('channel_name');
    $socketId = $request->input('socket_id');

    // Authorize: user can only listen to their own outlet/tenant channels
    $outletId = $user->outlet_id;
    $tenantId = $user->tenant_id;

    $allowed = str_contains($channelName, "dashboard.{$outletId}")
        || str_contains($channelName, "kitchen.{$outletId}")
        || str_contains($channelName, "tenant.{$tenantId}.outlet.{$outletId}");

    if (!$allowed) {
        return response()->json(['error' => 'Unauthorized channel'], 403);
    }

    $auth = $pusher->authorizeChannel($channelName, $socketId);
    return response()->json(json_decode($auth));
});
}); // end auth:sanctum middleware group
