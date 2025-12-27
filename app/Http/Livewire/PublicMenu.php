<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Product; //
use App\Models\Order;   //
use App\Models\OrderItem; //
use App\Models\Category; //

class PublicMenu extends Component
{
    public $tableId;
    public $category = 'All';
    public $cart = [];
    public $showCart = false;
    public $orderPlaced = false;

    public function mount($tableId = null)
    {
        $this->tableId = $tableId;
        
        // Security check: URLs must have ?key=...
        $validToken = request()->query('key');
        if ($validToken !== env('BAKERY_ORDER_KEY', 'bakery123')) {
            abort(403, 'Unauthorized. Please scan the QR code at your table.');
        }
    }

    public function addToCart($productId)
    {
        $product = Product::find($productId);
        if (!$product || !$product->is_available) return;

        if (isset($this->cart[$productId])) {
            $this->cart[$productId]['qty']++;
        } else {
            $this->cart[$productId] = [
                'name' => $product->name,
                'price' => $product->price,
                'qty' => 1,
                'image' => $product->image
            ];
        }
        $this->dispatchBrowserEvent('item-added');
    }

    public function updateQty($id, $delta)
    {
        if (isset($this->cart[$id])) {
            $this->cart[$id]['qty'] += $delta;
            if ($this->cart[$id]['qty'] <= 0) unset($this->cart[$id]);
        }
    }

    public function getTotalProperty()
    {
        return collect($this->cart)->sum(fn($item) => $item['price'] * $item['qty']);
    }

    public function checkout()
    {
        if (empty($this->cart)) return;

        $order = Order::create([
            'table_number' => $this->tableId ?? 'Walk-in',
            'total_amount' => $this->total,
            'status' => 'pending'
        ]);

        foreach ($this->cart as $id => $item) {
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $id,
                'quantity' => $item['qty'],
                'price' => $item['price']
            ]);
        }

        $this->cart = [];
        $this->orderPlaced = true;
        $this->showCart = false;
    }

    public function render()
    {
        $categories = Category::pluck('name')->toArray();
        $products = Product::where('is_available', true)
            ->when($this->category !== 'All', function($q) {
                return $q->whereHas('category', fn($c) => $c->where('name', $this->category));
            })->get();

        return view('livewire.public-menu', [
            'products' => $products,
            'categories' => array_merge(['All'], $categories)
        ])->layout('layouts.base'); //
    }
}