namespace App\Http\Livewire;
use Livewire\Component;

class PosSystem extends Component {
    public $cart = [];
    public $total = 0;

    public function addToCart($id, $name, $price) {
        if(isset($this->cart[$id])) {
            $this->cart[$id]['qty']++;
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
        $orderId = \DB::table('orders')->insertGetId(['total_amount' => $this->total, 'created_at' => now()]);
        foreach($this->cart as $id => $item) {
            \DB::table('order_items')->insert([
                'order_id' => $orderId, 'product_id' => $id, 
                'quantity' => $item['qty'], 'price' => $item['price']
            ]);
        }
        $this->reset('cart', 'total');
        session()->flash('status', 'Order processed successfully!');
    }

    public function render() {
        return view('livewire.pos-system', ['products' => \DB::table('products')->get()]);
    }
}