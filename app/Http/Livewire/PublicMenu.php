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
        
        // Cari meja berdasarkan nomor (Simple version)
        $table = Table::where('table_number', $tableNumber)->first();

        if (!$table) {
            abort(404, 'Meja tidak ditemukan atau kode salah.');
        }

        $this->tableId = $table->id;
        $this->outletId = $table->outlet_id;
        // Ambil tenant_id dari outlet relation
        $this->tenantId = $table->outlet ? $table->outlet->tenant_id : null;

        if (!$this->tenantId) abort(500, 'Konfigurasi Outlet Error');

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
            if($this->cart[$productId]['qty'] <= 0) {
                unset($this->cart[$productId]);
            }
        }
    }

    public function placeOrder()
    {
        $this->validate([
            'customerName' => 'required|min:3',
            'cart' => 'required|array|min:1'
        ], [
            'customerName.required' => 'Nama harus diisi',
            'cart.required' => 'Pilih minimal 1 menu'
        ]);

        DB::beginTransaction();
        try {
            $subtotal = 0;
            foreach($this->cart as $item) {
                $subtotal += $item['price'] * $item['qty'];
            }
            
            $tax = $subtotal * 0.11;
            $grandTotal = $subtotal + $tax;

            $order = Order::create([
                'tenant_id' => $this->tenantId,
                'outlet_id' => $this->outletId,
                'table_id' => $this->tableId,
                'order_number' => 'ORD-' . strtoupper(Str::random(6)),
                'order_type' => 'dine_in',
                'order_source' => 'qr_code',
                'customer_name' => $this->customerName,
                'status' => 'pending',
                'payment_status' => 'unpaid',
                'subtotal' => $subtotal,
                'tax_amount' => $tax,
                'grand_total' => $grandTotal,
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
            session()->flash('error', 'Gagal: ' . $e->getMessage());
        }
    }

    public function render()
    {
        // Menggunakan layout 'base' (layout kosong tanpa sidebar admin)
        return view('livewire.public-menu')->layout('layouts.base');
    }
}