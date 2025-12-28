<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Product;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PosSystem extends Component {
    public $cart = [];
    public $subtotal = 0;
    public $tax = 0;
    public $grand_total = 0;
    public $tax_rate = 0.11; // PPN 11% sesuai skema

    public function addToCart($id, $name, $price) {
        $product = Product::find($id);
        
        if (!$product || $product->stock_quantity <= 0) {
            session()->flash('error', "Stok habis!"); return;
        }

        if(isset($this->cart[$id])) {
            $this->cart[$id]['qty']++;
        } else {
            $this->cart[$id] = [
                'name' => $name, 
                'price' => $price, 
                'qty' => 1,
                'sku' => $product->sku
            ];
        }
        $this->calculateTotal();
    }

    public function calculateTotal() {
        $this->subtotal = array_sum(array_map(fn($item) => $item['price'] * $item['qty'], $this->cart));
        $this->tax = $this->subtotal * $this->tax_rate;
        $this->grand_total = $this->subtotal + $this->tax;
    }

    public function checkout() {
        if(empty($this->cart)) return;

        DB::transaction(function () {
            $user = Auth::user();
            
            $order = Order::create([
                'tenant_id' => $user->tenant_id, // Dari skema multi-tenant
                'outlet_id' => $user->outlet_id,
                'user_id'   => $user->id,
                'order_number' => 'ORD-' . strtoupper(Str::random(8)),
                'order_type' => 'dine_in',
                'subtotal' => $this->subtotal,
                'tax_amount' => $this->tax,
                'grand_total' => $this->grand_total,
                'status' => 'completed'
            ]);

            foreach($this->cart as $productId => $item) {
                DB::table('order_items')->insert([
                    'id' => (string) Str::uuid(),
                    'order_id' => $order->id,
                    'product_id' => $productId,
                    'product_name' => $item['name'],
                    'quantity' => $item['qty'],
                    'unit_price' => $item['price'],
                    'subtotal' => $item['price'] * $item['qty'],
                    'created_at' => now()
                ]);

                // Kurangi stok di tabel outlet_products sesuai skema
                DB::table('outlet_products')
                    ->where('product_id', $productId)
                    ->where('outlet_id', $user->outlet_id)
                    ->decrement('stock_quantity', $item['qty']);
            }
        });

        $this->reset(['cart', 'subtotal', 'tax', 'grand_total']);
        session()->flash('status', 'Transaksi Berhasil!');
    }

    public function render() {
        return view('livewire.pos-system', [
            'products' => Product::where('tenant_id', Auth::user()->tenant_id)->get()
        ]);
    }
}