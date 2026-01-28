<?php

namespace App\Http\Livewire\Public;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\TableSession;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\Attributes\On;

class Cart extends Component
{
    public array $items = [];
    public bool $isOpen = false;
    public float $subtotal = 0;
    public float $taxAmount = 0;
    public float $serviceCharge = 0;
    public float $grandTotal = 0;
    public int $itemCount = 0;
    
    // Table session context
    public ?string $sessionToken = null;
    public ?array $tableInfo = null;
    public bool $hasTableSession = false;

    // Payment functionality
    public bool $showPaymentModal = false;
    public ?string $selectedPaymentMethod = null;
    public array $availablePaymentMethods = [];
    public ?string $customerName = null;
    public ?string $customerPhone = null;

    public function mount()
    {
        $this->loadTableSession();
        $this->loadCart();
        $this->loadPaymentMethods();
    }

    /**
     * Load table session from cookie/request
     */
    protected function loadTableSession()
    {
        $this->sessionToken = request()->cookie('table_session_token');
        
        if ($this->sessionToken) {
            $session = TableSession::byToken($this->sessionToken)
                                   ->active()
                                   ->with(['table.tableArea', 'outlet'])
                                   ->first();
            
            if ($session) {
                $this->hasTableSession = true;
                $this->tableInfo = [
                    'session_id' => $session->id,
                    'table_id' => $session->table_id,
                    'table_number' => $session->table->table_number,
                    'table_area' => $session->table->tableArea?->name,
                    'outlet_id' => $session->outlet_id,
                    'outlet_name' => $session->outlet->name,
                    'tenant_id' => $session->tenant_id,
                    'tax_rate' => $session->outlet->tax_rate ?? 11,
                    'service_charge_rate' => $session->outlet->service_charge_rate ?? 0,
                ];
            }
        }
    }

    /**
     * Load available payment methods
     */
    protected function loadPaymentMethods()
    {
        if ($this->hasTableSession && $this->tableInfo) {
            $this->availablePaymentMethods = PaymentMethod::where('tenant_id', $this->tableInfo['tenant_id'])
                ->where('is_active', true)
                ->orderBy('name')
                ->get()
                ->map(fn($pm) => [
                    'id' => $pm->id,
                    'code' => $pm->code,
                    'name' => $pm->name,
                    'type' => $pm->payment_type,
                    'icon' => $this->getPaymentIcon($pm->payment_type),
                ])
                ->toArray();
        }
    }

    /**
     * Get payment icon based on type
     */
    protected function getPaymentIcon(string $type): string
    {
        return match($type) {
            'cash' => '💵',
            'card' => '💳',
            'digital_wallet' => '📱',
            'bank_transfer' => '🏦',
            'qris' => '📲',
            default => '💰',
        };
    }

    /**
     * Load cart from session
     */
    public function loadCart()
    {
        $cartKey = $this->getCartKey();
        $this->items = session($cartKey, []);
        $this->calculateTotals();
    }

    /**
     * Get cart key (unique per table session)
     */
    protected function getCartKey(): string
    {
        if ($this->sessionToken) {
            return 'cart_' . $this->sessionToken;
        }
        return 'cart';
    }

    /**
     * Calculate all totals
     */
    protected function calculateTotals()
    {
        $this->subtotal = 0;
        $this->itemCount = 0;

        foreach ($this->items as $item) {
            $this->subtotal += $item['price'] * $item['quantity'];
            $this->itemCount += $item['quantity'];
        }

        // Calculate tax and service charge if table session exists
        if ($this->hasTableSession && $this->tableInfo) {
            $taxRate = $this->tableInfo['tax_rate'] ?? 0;
            $serviceRate = $this->tableInfo['service_charge_rate'] ?? 0;
            
            $this->taxAmount = $this->subtotal * ($taxRate / 100);
            $this->serviceCharge = $this->subtotal * ($serviceRate / 100);
        } else {
            $this->taxAmount = 0;
            $this->serviceCharge = 0;
        }

        $this->grandTotal = $this->subtotal + $this->taxAmount + $this->serviceCharge;
    }

    #[On('add-to-cart')]
    public function addToCart(
        string $productId,
        ?string $variantId = null,
        int $quantity = 1,
        array $modifiers = [],
        ?string $notes = null
    ) {
        $product = Product::find($productId);
        if (!$product) return;

        $variant = $variantId ? ProductVariant::find($variantId) : null;

        // Generate unique cart key
        $cartKey = $this->generateCartKey($productId, $variantId, $modifiers);

        // Calculate item price
        $price = $product->base_price;
        if ($variant) {
            $price += $variant->price_adjustment;
        }

        // Add modifier prices
        $modifierTotal = 0;
        $modifierNames = [];
        foreach ($modifiers as $modifier) {
            $modifierTotal += $modifier['price'] ?? 0;
            $modifierNames[] = $modifier['name'] ?? '';
        }
        $price += $modifierTotal;

        if (isset($this->items[$cartKey])) {
            $this->items[$cartKey]['quantity'] += $quantity;
        } else {
            $this->items[$cartKey] = [
                'product_id' => $productId,
                'variant_id' => $variantId,
                'name' => $product->name,
                'variant_name' => $variant?->name,
                'sku' => $variant?->sku ?? $product->sku,
                'image' => $product->image_url,
                'price' => $price,
                'base_price' => $product->base_price,
                'quantity' => $quantity,
                'modifiers' => $modifiers,
                'modifier_names' => $modifierNames,
                'notes' => $notes,
            ];
        }

        $this->saveCart();
        $this->calculateTotals();

        $this->dispatch('cart-updated', [
            'message' => $product->name . ' ditambahkan ke keranjang',
            'count' => $this->itemCount,
        ]);

        $this->dispatch('cart-item-added');
    }

    #[On('update-cart-quantity')]
    public function updateQuantity(string $cartKey, int $quantity)
    {
        if ($quantity <= 0) {
            $this->removeItem($cartKey);
            return;
        }

        if (isset($this->items[$cartKey])) {
            $this->items[$cartKey]['quantity'] = $quantity;
            $this->saveCart();
            $this->calculateTotals();
        }
    }

    public function incrementQuantity(string $cartKey)
    {
        if (isset($this->items[$cartKey])) {
            $this->items[$cartKey]['quantity']++;
            $this->saveCart();
            $this->calculateTotals();
        }
    }

    public function decrementQuantity(string $cartKey)
    {
        if (isset($this->items[$cartKey])) {
            if ($this->items[$cartKey]['quantity'] > 1) {
                $this->items[$cartKey]['quantity']--;
                $this->saveCart();
                $this->calculateTotals();
            } else {
                $this->removeItem($cartKey);
            }
        }
    }

    #[On('remove-from-cart')]
    public function removeItem(string $cartKey)
    {
        if (isset($this->items[$cartKey])) {
            $itemName = $this->items[$cartKey]['name'];
            unset($this->items[$cartKey]);
            $this->saveCart();
            $this->calculateTotals();

            $this->dispatch('cart-updated', [
                'message' => $itemName . ' dihapus dari keranjang',
                'count' => $this->itemCount,
            ]);
        }
    }

    public function clearCart()
    {
        $this->items = [];
        $this->saveCart();
        $this->calculateTotals();
        $this->isOpen = false;

        $this->dispatch('cart-updated', [
            'message' => 'Keranjang dikosongkan',
            'count' => 0,
        ]);
    }

    /**
     * Open payment modal
     */
    public function proceedToPayment()
    {
        if (empty($this->items)) {
            $this->dispatch('order-error', ['message' => 'Keranjang kosong']);
            return;
        }

        if (!$this->hasTableSession) {
            $this->dispatch('order-error', ['message' => 'Silakan scan QR code di meja Anda']);
            return;
        }

        $this->showPaymentModal = true;
    }

    /**
     * Submit order with payment
     */
    public function submitOrderWithPayment(?string $orderNotes = null)
    {
        if (!$this->selectedPaymentMethod) {
            $this->dispatch('order-error', ['message' => 'Silakan pilih metode pembayaran']);
            return;
        }

        try {
            DB::beginTransaction();

            $session = TableSession::find($this->tableInfo['session_id']);
            if (!$session || !$session->isActive()) {
                throw new \Exception('Sesi meja tidak valid');
            }

            $orderNumber = $this->generateOrderNumber($session->outlet_id);

            // Create order (PAID)
            $order = Order::create([
                'tenant_id' => $this->tableInfo['tenant_id'],
                'outlet_id' => $this->tableInfo['outlet_id'],
                'table_id' => $this->tableInfo['table_id'],
                'table_session_id' => $session->id,
                'order_number' => $orderNumber,
                'order_type' => 'dine_in',
                'order_source' => 'qr_scan',
                'status' => 'confirmed',
                'payment_status' => 'paid',
                'customer_name' => $this->customerName,
                'customer_phone' => $this->customerPhone,
                'subtotal' => $this->subtotal,
                'tax_amount' => $this->taxAmount,
                'service_charge' => $this->serviceCharge,
                'grand_total' => $this->grandTotal,
                'guest_count' => $session->guest_count,
                'notes' => $orderNotes,
                'ordered_at' => now(),
                'confirmed_at' => now(),
            ]);

            // Create order items
            foreach ($this->items as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'product_variant_id' => $item['variant_id'],
                    'product_name' => $item['name'],
                    'variant_name' => $item['variant_name'],
                    'sku' => $item['sku'] ?? null,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['price'],
                    'subtotal' => $item['price'] * $item['quantity'],
                    'modifiers' => !empty($item['modifiers']) ? json_encode($item['modifiers']) : null,
                    'notes' => $item['notes'],
                    'kitchen_status' => 'pending',
                ]);
            }

            // Create payment record
            $paymentMethod = PaymentMethod::find($this->selectedPaymentMethod);
            $paymentNumber = 'PAY-' . now()->format('YmdHis') . '-' . substr($order->id, 0, 8);

            Payment::create([
                'tenant_id' => $this->tableInfo['tenant_id'],
                'outlet_id' => $this->tableInfo['outlet_id'],
                'order_id' => $order->id,
                'payment_method_id' => $this->selectedPaymentMethod,
                'payment_number' => $paymentNumber,
                'transaction_type' => 'payment',
                'amount' => $this->grandTotal,
                'net_amount' => $this->grandTotal,
                'status' => 'completed',
                'paid_at' => now(),
            ]);

            $session->update(['status' => 'ordering']);

            DB::commit();

            // Clear cart
            $this->items = [];
            $this->saveCart();
            $this->calculateTotals();
            $this->isOpen = false;
            $this->showPaymentModal = false;
            $this->reset(['selectedPaymentMethod', 'customerName', 'customerPhone']);

            $this->dispatch('order-success', [
                'message' => 'Pembayaran berhasil!',
                'order_number' => $orderNumber,
                'order_id' => $order->id,
                'payment_method' => $paymentMethod->name,
                'amount' => $this->formatPrice($this->grandTotal),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('order-error', ['message' => 'Gagal: ' . $e->getMessage()]);
        }
    }

    /**
     * Submit order without payment (pay later)
     */
    public function submitOrder(?string $orderNotes = null)
    {
        if (empty($this->items)) {
            $this->dispatch('order-error', ['message' => 'Keranjang kosong']);
            return;
        }

        if (!$this->hasTableSession || !$this->tableInfo) {
            $this->dispatch('order-error', ['message' => 'Silakan scan QR code di meja Anda']);
            return;
        }

        try {
            DB::beginTransaction();

            $session = TableSession::find($this->tableInfo['session_id']);
            if (!$session || !$session->isActive()) {
                throw new \Exception('Sesi meja tidak valid');
            }

            $orderNumber = $this->generateOrderNumber($session->outlet_id);

            // Create order (UNPAID)
            $order = Order::create([
                'tenant_id' => $this->tableInfo['tenant_id'],
                'outlet_id' => $this->tableInfo['outlet_id'],
                'table_id' => $this->tableInfo['table_id'],
                'table_session_id' => $session->id,
                'order_number' => $orderNumber,
                'order_type' => 'dine_in',
                'order_source' => 'qr_scan',
                'status' => 'pending',
                'payment_status' => 'unpaid',
                'subtotal' => $this->subtotal,
                'tax_amount' => $this->taxAmount,
                'service_charge' => $this->serviceCharge,
                'grand_total' => $this->grandTotal,
                'guest_count' => $session->guest_count,
                'notes' => $orderNotes,
                'ordered_at' => now(),
            ]);

            // Create order items
            foreach ($this->items as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'product_variant_id' => $item['variant_id'],
                    'product_name' => $item['name'],
                    'variant_name' => $item['variant_name'],
                    'sku' => $item['sku'] ?? null,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['price'],
                    'subtotal' => $item['price'] * $item['quantity'],
                    'modifiers' => !empty($item['modifiers']) ? json_encode($item['modifiers']) : null,
                    'notes' => $item['notes'],
                    'kitchen_status' => 'pending',
                ]);
            }

            $session->update(['status' => 'ordering']);

            DB::commit();

            // Clear cart
            $this->items = [];
            $this->saveCart();
            $this->calculateTotals();
            $this->isOpen = false;

            $this->dispatch('order-success', [
                'message' => 'Pesanan berhasil dikirim!',
                'order_number' => $orderNumber,
                'order_id' => $order->id,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('order-error', ['message' => 'Gagal: ' . $e->getMessage()]);
        }
    }
    
    /**
     * Generate order number
     */
    protected function generateOrderNumber(string $outletId): string
    {
        $date = now()->format('Ymd');
        $prefix = 'ORD';
        
        $count = Order::where('outlet_id', $outletId)
                      ->whereDate('created_at', today())
                      ->count() + 1;
        
        return sprintf('%s-%s-%04d', $prefix, $date, $count);
    }

    public function closePaymentModal()
    {
        $this->showPaymentModal = false;
        $this->selectedPaymentMethod = null;
    }

    public function openCart()
    {
        $this->isOpen = true;
    }

    public function closeCart()
    {
        $this->isOpen = false;
    }

    public function toggleCart()
    {
        $this->isOpen = !$this->isOpen;
    }

    protected function generateCartKey(string $productId, ?string $variantId, array $modifiers): string
    {
        $modifierIds = collect($modifiers)->pluck('id')->sort()->implode('-');
        return md5($productId . '-' . ($variantId ?? 'none') . '-' . $modifierIds);
    }

    protected function saveCart()
    {
        $cartKey = $this->getCartKey();
        session([$cartKey => $this->items]);
    }

    public function formatPrice($price): string
    {
        return 'Rp ' . number_format($price, 0, ',', '.');
    }

    public function render()
    {
        return view('livewire.public.cart');
    }
}