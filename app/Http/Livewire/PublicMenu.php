<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Product;
use App\Models\Category;
use App\Models\Table;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class PublicMenu extends Component
{
    public $tableNumber;
    public $tableId;
    public $outletId;
    public $tenantId;
    
    public $categories = [];
    public $products = [];
    public $selectedCategory = 'all';
    public $cart = [];
    
    public $customerName;
    public $orderSuccess = false;
    public $orderNumberStr = '';

    public function mount($tableNumber)
    {
        $this->tableNumber = $tableNumber;
        $table = Table::where('table_number', $tableNumber)->first();

        if (!$table) {
            abort(404, 'Table not found. Please scan a valid QR.');
        }

        $this->tableId = $table->id;
        $this->outletId = $table->outlet_id;
        
        // Robust check for Tenant ID
        $this->tenantId = $table->outlet ? $table->outlet->tenant_id : null;

        // Fallback: If table has no outlet, try to find the first tenant (for testing)
        if (!$this->tenantId) {
             $firstTenant = \App\Models\Tenant::first();
             $this->tenantId = $firstTenant ? $firstTenant->id : null;
        }

        if (!$this->tenantId) {
            abort(500, 'System Configuration Error: No Tenant associated with this outlet.');
        }

        $this->categories = Category::where('tenant_id', $this->tenantId)->get();
        $this->loadProducts();
    }

    public function loadProducts()
    {
        $query = Product::where('tenant_id', $this->tenantId)->where('is_available', true);
        if ($this->selectedCategory != 'all') {
            $query->where('category_id', $this->selectedCategory);
        }
        $this->products = $query->get();
    }

    public function selectCategory($catId)
    {
        $this->selectedCategory = $catId;
        $this->loadProducts();
    }

    public function addToCart($productId)
    {
        $product = Product::find($productId);
        if (!$product) return;
        
        if(isset($this->cart[$productId])) {
            $this->cart[$productId]['qty']++;
        } else {
            $this->cart[$productId] = [
                'id' => $product->id,
                'name' => $product->name,
                'price' => $product->base_price,
                'qty' => 1,
                'image' => $product->image_url
            ];
        }
    }

    public function updateQty($productId, $change)
    {
        if(isset($this->cart[$productId])) {
            $this->cart[$productId]['qty'] += $change;
            if($this->cart[$productId]['qty'] <= 0) unset($this->cart[$productId]);
        }
    }

    public function placeOrder()
    {
        $this->validate(['customerName' => 'required|min:2', 'cart' => 'required|array|min:1']);

        DB::beginTransaction();
        try {
            $subtotal = collect($this->cart)->sum(fn($i) => $i['price'] * $i['qty']);
            $tax = $subtotal * 0.11;

            $order = Order::create([
                'tenant_id' => $this->tenantId,
                'outlet_id' => $this->outletId,
                'table_id' => $table->id ?? null,
                'order_number' => 'ORD-' . strtoupper(Str::random(6)),
                'customer_name' => $this->customerName,
                'status' => 'pending',
                'grand_total' => $subtotal + $tax,
                'ordered_at' => now(),
            ]);

            foreach($this->cart as $item) {
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
            $this->orderSuccess = true;
            $this->orderNumberStr = $order->order_number;
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Failed: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.public-menu')->layout('layouts.base');
    }
}