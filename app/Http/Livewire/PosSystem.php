<?php
namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Product;
use App\Models\Order;
use App\Models\Category;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PosSystem extends Component {
    public $cart = [];
    public $total = 0;
    public $search = '';
    public $selectedCategory = null;

    public function addToCart($id, $name, $price) {
        $product = Product::find($id);
        
        // Feature: Stock Validation
        if ($product->stock <= 0) {
            session()->flash('error', "Item $name is out of stock!");
            return;
        }

        if(isset($this->cart[$id])) {
            if ($this->cart[$id]['qty'] < $product->stock) {
                $this->cart[$id]['qty']++;
            } else {
                session()->flash('error', "Maximum stock reached for $name");
                return;
            }
        } else {
            $this->cart[$id] = ['name' => $name, 'price' => $price, 'qty' => 1];
        }
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
                'status' => 'completed'
            ]);

            foreach($this->cart as $id => $item) {
                DB::table('order_items')->insert([
                    'order_id' => $order->id,
                    'product_id' => $id,
                    'quantity' => $item['qty'],
                    'price' => $item['price'],
                    'created_at' => now()
                ]);
                Product::find($id)->decrement('stock', $item['qty']); // Auto-reduce stock
            }
        });

        $this->reset(['cart', 'total']);
        session()->flash('status', 'Transaction Successful!');
    }

    public function render() {
        $query = Product::where('user_id', Auth::id())
            ->where('name', 'like', '%'.$this->search.'%');
        
        if ($this->selectedCategory) {
            $query->where('category_id', $this->selectedCategory);
        }

        return view('livewire.pos-system', [
            'products' => $query->get(),
            'categories' => Category::where('user_id', Auth::id())->get()
        ]);
    }
}