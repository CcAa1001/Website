<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Category;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PosSystem extends Component
{
    public $products = [];
    public $categories = [];
    public $cart = [];
    public $selectedCategory = null;
    public $search = '';
    
    public $subtotal = 0;
    public $tax = 0;
    public $grand_total = 0;
    public $taxRate = 0.11;

    public $customerName = 'Guest';

    public function mount()
    {
        $this->loadCategories();
        $this->loadProducts();
    }

    public function loadCategories()
    {
        $user = auth()->user();
        if($user && $user->tenant_id) {
            $this->categories = Category::where('tenant_id', $user->tenant_id)->get();
        }
    }

    public function loadProducts()
    {
        $user = auth()->user();
        if(!$user) return;

        $query = Product::where('tenant_id', $user->tenant_id)->where('is_available', true);
        if ($this->selectedCategory) $query->where('category_id', $this->selectedCategory);
        if ($this->search) $query->where('name', 'ilike', '%' . $this->search . '%');

        $this->products = $query->orderBy('name')->get();
    }

    public function filterCategory($categoryId)
    {
        $this->selectedCategory = $categoryId;
        $this->loadProducts();
    }

    public function updatedSearch() { $this->loadProducts(); }

    public function addToCart($productId)
    {
        $product = Product::find($productId);
        if (!$product) return;

        if (isset($this->cart[$productId])) {
            $this->cart[$productId]['qty']++;
        } else {
            $this->cart[$productId] = [
                'id' => $product->id, 'name' => $product->name,
                'price' => $product->base_price, 'qty' => 1, 'sku' => $product->sku
            ];
        }
        $this->calculateTotal();
    }

    public function removeFromCart($productId)
    {
        if (isset($this->cart[$productId])) {
            $this->cart[$productId]['qty']--;
            if($this->cart[$productId]['qty'] <= 0) unset($this->cart[$productId]);
        }
        $this->calculateTotal();
    }

    public function calculateTotal()
    {
        $this->subtotal = 0;
        foreach ($this->cart as $item) $this->subtotal += $item['price'] * $item['qty'];
        $this->tax = $this->subtotal * $this->taxRate;
        $this->grand_total = $this->subtotal + $this->tax;
    }

    public function checkout()
    {
        if (empty($this->cart)) return;

        DB::beginTransaction();
        try {
            $user = auth()->user();
            $order = Order::create([
                'tenant_id' => $user->tenant_id,
                'outlet_id' => $user->outlet_id,
                'user_id' => $user->id,
                'order_number' => 'POS-' . strtoupper(Str::random(8)),
                'order_type' => 'dine_in',
                'customer_name' => $this->customerName,
                'status' => 'paid',
                'payment_status' => 'paid',
                'subtotal' => $this->subtotal,
                'tax_amount' => $this->tax,
                'grand_total' => $this->grand_total,
                'ordered_at' => now(),
            ]);

            foreach ($this->cart as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['id'],
                    'product_name' => $item['name'],
                    'quantity' => $item['qty'],
                    'unit_price' => $item['price'],
                    'subtotal' => $item['price'] * $item['qty'],
                ]);
            }

            DB::commit();
            $this->cart = [];
            $this->calculateTotal();
            session()->flash('success', 'Order Completed!');
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', $e->getMessage());
        }
    }

    public function render() { return view('livewire.pos-system'); }
}