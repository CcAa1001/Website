<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Category;
use App\Models\Table;
use App\Models\PaymentMethod;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class POSSystem extends Component
{
    // ==================== VIEW STATE ====================
    public $activeView = 'products'; // products, checkout, payment
    public $showModifierModal = false;
    public $showPaymentModal = false;
    public $showTableSelector = false;
    public $showParkedOrders = false;
    
    // ==================== PRODUCT SELECTION ====================
    public $products = [];
    public $categories = [];
    public $selectedCategory = null;
    public $search = '';
    
    // ==================== CART ====================
    public $cart = [];
    public $cartItemCounter = 0; // Unique key for cart items
    
    // ==================== CURRENT ITEM (for modifiers) ====================
    public $currentProductId = null; // Store ID instead of object
    public $selectedModifiers = [];
    public $specialInstructions = '';
    public $itemQuantity = 1;
    
    // ==================== ORDER DETAILS ====================
    public $orderType = 'dine_in'; // dine_in, takeaway
    public $selectedTable = null;
    public $customerName = '';
    public $customerPhone = '';
    public $guestCount = 1;
    public $orderNotes = '';
    public $trackInKitchen = true; // ← NEW: Send to Kitchen Board toggle
    
    // ==================== PAYMENT ====================
    public $paymentMethods = [];
    public $selectedPayments = []; // For split payment
    public $cashReceived = 0;
    public $cashChange = 0;
    
    // ==================== CALCULATIONS ====================
    public $subtotal = 0;
    public $taxAmount = 0;
    public $serviceCharge = 0;
    public $discountAmount = 0;
    public $grandTotal = 0;
    public $taxRate = 0.11; // 11% PPN
    public $serviceChargeRate = 0.05; // 5% service charge (optional)
    public $applyServiceCharge = false;
    
    // ==================== PARKED ORDERS ====================
    public $parkedOrders = [];
    
    // ==================== LISTENERS ====================
    protected $listeners = [
        'addModifiersToCart' => 'addModifiersToCart',
        'paymentCompleted' => 'paymentCompleted',
    ];

    public function mount()
    {
        $this->loadCategories();
        $this->loadProducts();
        $this->loadPaymentMethods();
        $this->loadParkedOrders();
    }

    // ==================== DATA LOADING ====================

    public function loadCategories()
    {
        $user = auth()->user();
        if ($user && $user->tenant_id) {
            $this->categories = Category::where('tenant_id', $user->tenant_id)
                ->where('is_active', true)
                ->withCount(['products' => function($q) {
                    $q->where('is_available', true);
                }])
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get();
        }
    }

    public function loadProducts()
    {
        $user = auth()->user();
        if (!$user) return;

        $query = Product::where('tenant_id', $user->tenant_id)
            ->where('is_available', true)
            ->with(['category', 'modifierGroups.modifiers', 'variants', 'primaryImage']);

        if ($this->selectedCategory) {
            $query->where('category_id', $this->selectedCategory);
        }

        if ($this->search) {
            $query->where(function($q) {
                $q->where('name', 'ilike', '%' . $this->search . '%')
                  ->orWhere('sku', 'ilike', '%' . $this->search . '%');
            });
        }

        $this->products = $query->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }

    public function loadPaymentMethods()
    {
        $user = auth()->user();
        if ($user && $user->tenant_id) {
            $this->paymentMethods = PaymentMethod::where('tenant_id', $user->tenant_id)
                ->where('is_active', true)
                ->orderBy('name')
                ->get();
        }
    }

    public function loadParkedOrders()
    {
        $user = auth()->user();
        if ($user) {
            try {
                $parkedOrders = session()->get('parked_orders_' . $user->id, []);
                
                $cleanedOrders = [];
                foreach ($parkedOrders as $order) {
                    if (is_array($order) && isset($order['id']) && is_string($order['id'])) {
                        $cleanedOrders[] = $order;
                    }
                }
                
                $this->parkedOrders = $cleanedOrders;
                
                if (count($cleanedOrders) !== count($parkedOrders)) {
                    session()->put('parked_orders_' . $user->id, $cleanedOrders);
                }
            } catch (\Exception $e) {
                $this->parkedOrders = [];
                session()->forget('parked_orders_' . $user->id);
            }
        }
    }

    // ==================== PRODUCT SELECTION ====================

    public function filterCategory($categoryId)
    {
        $this->selectedCategory = $categoryId;
        $this->loadProducts();
    }

    public function updatedSearch()
    {
        $this->loadProducts();
    }

    public function selectProduct($productId)
    {
        $product = Product::with(['modifierGroups.modifiers', 'variants', 'primaryImage'])
            ->find($productId);
        
        if (!$product) return;

        if ($product->modifierGroups->isNotEmpty() || $product->variants->isNotEmpty()) {
            $this->currentProductId = $productId;
            $this->selectedModifiers = [];
            $this->specialInstructions = '';
            $this->itemQuantity = 1;
            $this->showModifierModal = true;
        } else {
            $this->addToCartDirect($product);
        }
    }

    public function addToCartDirect($product)
    {
        $cartKey = 'item_' . $this->cartItemCounter++;
        
        $this->cart[$cartKey] = [
            'product_id' => $product->id,
            'product_name' => $product->name,
            'product_image' => $product->medium_image,
            'sku' => $product->sku,
            'base_price' => $product->base_price,
            'quantity' => 1,
            'modifiers' => [],
            'modifiers_total' => 0,
            'special_instructions' => '',
            'item_total' => $product->base_price,
        ];

        $this->calculateTotals();
        
        $this->dispatch('show-toast', [
            'message' => $product->name . ' ditambahkan ke keranjang',
            'type' => 'success'
        ]);
    }

    public function closeModifierModal()
    {
        $this->showModifierModal = false;
        $this->currentProductId = null;
        $this->selectedModifiers = [];
        $this->specialInstructions = '';
        $this->itemQuantity = 1;
    }

    public function addWithModifiers()
    {
        if (!$this->currentProductId) return;

        $product = Product::find($this->currentProductId);
        if (!$product) return;

        $cartKey = 'item_' . $this->cartItemCounter++;
        
        $modifiersTotal = 0;
        $modifiersList = [];
        
        foreach ($this->selectedModifiers as $modifierId => $selected) {
            if ($selected) {
                $modifier = \App\Models\Modifier::find($modifierId);
                if ($modifier) {
                    $modifiersTotal += $modifier->price;
                    $modifiersList[] = [
                        'id' => $modifier->id,
                        'name' => $modifier->name,
                        'price' => $modifier->price,
                    ];
                }
            }
        }

        $itemTotal = ($product->base_price + $modifiersTotal) * $this->itemQuantity;

        $this->cart[$cartKey] = [
            'product_id' => $product->id,
            'product_name' => $product->name,
            'product_image' => $product->medium_image,
            'sku' => $product->sku,
            'base_price' => $product->base_price,
            'quantity' => $this->itemQuantity,
            'modifiers' => $modifiersList,
            'modifiers_total' => $modifiersTotal,
            'special_instructions' => $this->specialInstructions,
            'item_total' => $itemTotal,
        ];

        $this->calculateTotals();
        $this->closeModifierModal();
        
        $this->dispatch('show-toast', [
            'message' => $product->name . ' ditambahkan ke keranjang',
            'type' => 'success'
        ]);
    }

    // ==================== CART MANAGEMENT ====================

    public function updateQuantity($cartKey, $action)
    {
        if (!isset($this->cart[$cartKey])) return;

        if ($action === 'increase') {
            $this->cart[$cartKey]['quantity']++;
        } elseif ($action === 'decrease') {
            $this->cart[$cartKey]['quantity']--;
            
            if ($this->cart[$cartKey]['quantity'] <= 0) {
                unset($this->cart[$cartKey]);
                $this->calculateTotals();
                return;
            }
        }

        $item = $this->cart[$cartKey];
        $this->cart[$cartKey]['item_total'] = ($item['base_price'] + $item['modifiers_total']) * $item['quantity'];
        
        $this->calculateTotals();
    }

    public function removeFromCart($cartKey)
    {
        if (isset($this->cart[$cartKey])) {
            unset($this->cart[$cartKey]);
            $this->calculateTotals();
            
            $this->dispatch('show-toast', [
                'message' => 'Item dihapus dari keranjang',
                'type' => 'info'
            ]);
        }
    }

    public function clearCart()
    {
        $this->cart = [];
        $this->calculateTotals();
        $this->resetOrderDetails();
        
        $this->dispatch('show-toast', [
            'message' => 'Keranjang dikosongkan',
            'type' => 'info'
        ]);
    }

    public function calculateTotals()
    {
        $this->subtotal = 0;
        
        foreach ($this->cart as $item) {
            $this->subtotal += $item['item_total'];
        }

        $this->taxAmount = $this->subtotal * $this->taxRate;
        $this->serviceCharge = $this->applyServiceCharge ? ($this->subtotal * $this->serviceChargeRate) : 0;
        $this->grandTotal = $this->subtotal + $this->taxAmount + $this->serviceCharge - $this->discountAmount;
    }

    // ==================== ORDER MANAGEMENT ====================

    public function setOrderType($type)
    {
        $this->orderType = $type;
        
        if ($type === 'takeaway') {
            $this->selectedTable = null;
            $this->applyServiceCharge = false;
        } else {
            $this->applyServiceCharge = true;
        }
        
        $this->calculateTotals();
    }

    public function selectTable($tableId)
    {
        $this->selectedTable = $tableId;
        $this->showTableSelector = false;
    }

    public function openTableSelector()
    {
        $this->showTableSelector = true;
    }

    public function closeTableSelector()
    {
        $this->showTableSelector = false;
    }

    // ==================== PARK ORDER ====================

    public function parkOrder()
    {
        if (empty($this->cart)) {
            $this->dispatch('show-toast', [
                'message' => 'Keranjang kosong',
                'type' => 'error'
            ]);
            return;
        }

        $user = auth()->user();
        
        $tableNumber = null;
        if ($this->selectedTable) {
            $table = Table::find($this->selectedTable);
            $tableNumber = $table ? $table->table_number : null;
        }

        $parkedOrder = [
            'id' => Str::uuid()->toString(),
            'parked_at' => now()->toDateTimeString(),
            'cart' => $this->cart,
            'order_type' => $this->orderType,
            'selected_table' => $this->selectedTable,
            'table_number' => $tableNumber,
            'customer_name' => $this->customerName,
            'customer_phone' => $this->customerPhone,
            'guest_count' => $this->guestCount,
            'order_notes' => $this->orderNotes,
            'track_in_kitchen' => $this->trackInKitchen,  // ← NEW: preserve toggle state
            'subtotal' => $this->subtotal,
            'tax_amount' => $this->taxAmount,
            'service_charge' => $this->serviceCharge,
            'grand_total' => $this->grandTotal,
        ];

        $parkedOrders = session()->get('parked_orders_' . $user->id, []);
        $parkedOrders[] = $parkedOrder;
        session()->put('parked_orders_' . $user->id, $parkedOrders);

        $this->loadParkedOrders();
        $this->clearCart();
        
        $this->dispatch('show-toast', [
            'message' => 'Order berhasil di-park',
            'type' => 'success'
        ]);
    }

    public function loadParkedOrder($index)
    {
        if (!isset($this->parkedOrders[$index])) return;

        $parkedOrder = $this->parkedOrders[$index];
        
        $this->cart = $parkedOrder['cart'];
        $this->orderType = $parkedOrder['order_type'];
        $this->selectedTable = $parkedOrder['selected_table'];
        $this->customerName = $parkedOrder['customer_name'];
        $this->customerPhone = $parkedOrder['customer_phone'];
        $this->guestCount = $parkedOrder['guest_count'];
        $this->orderNotes = $parkedOrder['order_notes'];
        $this->trackInKitchen = $parkedOrder['track_in_kitchen'] ?? true;  // ← NEW: restore toggle
        
        $user = auth()->user();
        $parkedOrders = session()->get('parked_orders_' . $user->id, []);
        unset($parkedOrders[$index]);
        $parkedOrders = array_values($parkedOrders);
        session()->put('parked_orders_' . $user->id, $parkedOrders);
        
        $this->loadParkedOrders();
        $this->calculateTotals();
        $this->showParkedOrders = false;
        
        $this->dispatch('show-toast', [
            'message' => 'Order berhasil dimuat',
            'type' => 'success'
        ]);
    }

    public function deleteParkedOrder($index)
    {
        $user = auth()->user();
        $parkedOrders = session()->get('parked_orders_' . $user->id, []);
        
        if (isset($parkedOrders[$index])) {
            unset($parkedOrders[$index]);
            $parkedOrders = array_values($parkedOrders);
            session()->put('parked_orders_' . $user->id, $parkedOrders);
            $this->loadParkedOrders();
            
            $this->dispatch('show-toast', [
                'message' => 'Parked order dihapus',
                'type' => 'info'
            ]);
        }
    }

    // ==================== CHECKOUT ====================

    public function proceedToPayment()
    {
        if (empty($this->cart)) {
            $this->dispatch('show-toast', [
                'message' => '⚠️ Keranjang kosong',
                'type' => 'error'
            ]);
            return;
        }

        if ($this->orderType === 'dine_in' && !$this->selectedTable) {
            $this->dispatch('show-toast', [
                'message' => '⚠️ Pilih meja terlebih dahulu untuk Dine In!',
                'type' => 'warning'
            ]);
            $this->showTableSelector = true;
            return;
        }

        $this->selectedPayments = [
            0 => [
                'method_id' => null,
                'cash_received' => 0,
            ]
        ];

        $this->showPaymentModal = true;
    }

    public function closePaymentModal()
    {
        $this->showPaymentModal = false;
        $this->selectedPayments = [];
        $this->cashReceived = 0;
    }

    public function completeOrder()
    {
        if (empty($this->cart)) {
            $this->dispatch('show-toast', [
                'message' => 'Keranjang kosong',
                'type' => 'error'
            ]);
            return;
        }

        if (!isset($this->selectedPayments[0]['method_id']) || empty($this->selectedPayments[0]['method_id'])) {
            $this->dispatch('show-toast', [
                'message' => 'Pilih metode pembayaran',
                'type' => 'error'
            ]);
            return;
        }

        $paymentMethod = PaymentMethod::find($this->selectedPayments[0]['method_id']);
        
        if (!$paymentMethod) {
            $this->dispatch('show-toast', [
                'message' => 'Metode pembayaran tidak valid',
                'type' => 'error'
            ]);
            return;
        }
        
        if ($paymentMethod->payment_type === 'cash') {
            $cashReceived = $this->selectedPayments[0]['cash_received'] ?? 0;
            
            if ($cashReceived < $this->grandTotal) {
                $this->dispatch('show-toast', [
                    'message' => 'Uang yang diterima kurang dari total (Rp ' . number_format($this->grandTotal, 0, ',', '.') . ')',
                    'type' => 'error'
                ]);
                return;
            }
        }

        DB::beginTransaction();
        try {
            $user = auth()->user();
            
            // ✅ Create order with track_in_kitchen
            $order = Order::create([
                'tenant_id' => $user->tenant_id,
                'outlet_id' => $user->outlet_id,
                'user_id' => $user->id,
                'table_id' => $this->selectedTable,
                'order_number' => $this->generateOrderNumber(),
                'order_type' => $this->orderType,
                'order_source' => 'pos',
                'track_in_kitchen' => $this->trackInKitchen,  // ← NEW
                'customer_name' => $this->customerName ?: 'Guest',
                'customer_phone' => $this->customerPhone,
                'guest_count' => $this->guestCount,
                'status' => 'confirmed',
                'payment_status' => 'paid',
                'subtotal' => $this->subtotal,
                'tax_amount' => $this->taxAmount,
                'service_charge' => $this->serviceCharge,
                'discount_amount' => $this->discountAmount,
                'grand_total' => $this->grandTotal,
                'notes' => $this->orderNotes,
                'ordered_at' => now(),
                'confirmed_at' => now(),
            ]);

            // Create order items
            foreach ($this->cart as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'product_name' => $item['product_name'],
                    'sku' => $item['sku'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['base_price'],
                    'subtotal' => $item['item_total'],
                    'modifiers' => $item['modifiers'],
                    'notes' => $item['special_instructions'],
                    'kitchen_status' => 'pending',
                ]);
            }

            // Create payment
            $cashReceived = $paymentMethod->payment_type === 'cash' 
                ? ($this->selectedPayments[0]['cash_received'] ?? 0) 
                : 0;
            
            $cashChange = $paymentMethod->payment_type === 'cash' 
                ? ($cashReceived - $this->grandTotal) 
                : 0;

            Payment::create([
                'tenant_id' => $user->tenant_id,
                'outlet_id' => $user->outlet_id,
                'order_id' => $order->id,
                'payment_method_id' => $this->selectedPayments[0]['method_id'],
                'user_id' => $user->id,
                'payment_number' => $this->generatePaymentNumber(),
                'transaction_type' => 'payment',
                'amount' => $this->grandTotal,
                'net_amount' => $this->grandTotal,
                'status' => 'completed',
                'cash_received' => $cashReceived,
                'cash_change' => $cashChange,
                'paid_at' => now(),
            ]);

            DB::commit();

            // ✅ NEW: Broadcast to kitchen if tracked
            if ($this->trackInKitchen) {
                try {
                    broadcast(new \App\Events\NewOrderReceived(
                        $order->load(['table', 'items'])
                    ))->toOthers();
                } catch (\Exception $e) {
                    \Log::warning('Broadcast failed: ' . $e->getMessage());
                }
            }

            // Clear cart and reset
            $this->clearCart();
            $this->closePaymentModal();
            
            $kitchenMsg = $this->trackInKitchen ? ' (dikirim ke Kitchen Board)' : '';
            $this->dispatch('show-toast', [
                'message' => '✅ Order berhasil! #' . $order->order_number . $kitchenMsg,
                'type' => 'success'
            ]);

            $this->dispatch('print-receipt', ['order_id' => $order->id]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            $this->dispatch('show-toast', [
                'message' => 'Error: ' . $e->getMessage(),
                'type' => 'error'
            ]);
        }
    }

    // ==================== HELPERS ====================

    private function generateOrderNumber()
    {
        $prefix = $this->orderType === 'dine_in' ? 'DI' : 'TA';
        return $prefix . '-' . now()->format('Ymd') . '-' . strtoupper(Str::random(6));
    }

    private function generatePaymentNumber()
    {
        return 'PAY-' . now()->format('Ymd') . '-' . strtoupper(Str::random(8));
    }

    private function resetOrderDetails()
    {
        $this->orderType = 'dine_in';
        $this->selectedTable = null;
        $this->customerName = '';
        $this->customerPhone = '';
        $this->guestCount = 1;
        $this->orderNotes = '';
        $this->trackInKitchen = true;  // ← Reset to default ON
        $this->applyServiceCharge = false;
    }

    public function render()
    {
        $tables = [];
        
        if ($this->orderType === 'dine_in') {
            $user = auth()->user();
            $tables = Table::where('outlet_id', $user->outlet_id)
                ->where('is_active', true)
                ->orderBy('table_number')
                ->get();
        }

        return view('livewire.pos-system', [
            'tables' => $tables,
        ]);
    }
}