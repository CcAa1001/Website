<?php
namespace App\Http\Livewire;
use Livewire\Component;
use App\Models\Product;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PosSystem extends Component {
    public $cart = [];
    public $total = 0;
    public $payment_method = 'cash'; // Payment Gateway Placeholder

    public function addToCart($id, $name, $price) {
        $product = Product::find($id);
        if ($product->stock <= 0) {
            session()->flash('error', "Out of stock!"); return;
        }
        if(isset($this->cart[$id])) { $this->cart[$id]['qty']++; } 
        else { $this->cart[$id] = ['name' => $name, 'price' => $price, 'qty' => 1]; }
        $this->calculateTotal();
    }

    public function calculateTotal() {
        $this->total = array_sum(array_map(fn($item) => $item['price'] * $item['qty'], $this->cart));
    }

    public function checkout() {
        if(empty($this->cart)) return;
        DB::transaction(function () {
            $order = Order::create([
                'user_id' => Auth::id(), 
                'total_amount' => $this->total,
                'payment_method' => $this->payment_method
            ]);
            foreach($this->cart as $id => $item) {
                DB::table('order_items')->insert([
                    'order_id' => $order->id, 'product_id' => $id,
                    'quantity' => $item['qty'], 'price' => $item['price'], 'created_at' => now()
                ]);
                Product::find($id)->decrement('stock', $item['qty']); // Inventory reduction
            }
        });
        $this->reset(['cart', 'total']);
        session()->flash('status', 'Transaction Completed!');
    }

    public function render() {
        return view('livewire.pos-system', [
            'products' => Product::where('user_id', Auth::id())->get()
        ]);
    }
}