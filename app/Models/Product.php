<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;

class PublicMenu extends Component
{
    public $tableId;
    public $category = 'All';
    public $cart = [];
    public $showCart = false;
    public $orderPlaced = false;

    // Capture the table ID from the URL parameter
    public function mount($tableId = null)
    {
        $this->tableId = $tableId;
    }

    public function setCategory($cat)
    {
        $this->category = $cat;
    }

    public function addToCart($productId)
    {
        $product = Product::find($productId);
        if (!$product) return;

        if (isset($this->cart[$productId])) {
            $this->cart[$productId]['qty']++;
        } else {
            $this->cart[$productId] = [
                'name' => $product->name,
                'price' => $product->price,
                'qty' => 1,
                'image' => $product->image ?? 'assets/img/products/product-1-min.jpg'
            ];
        }
        $this->emit('notify', ['type' => 'success', 'message' => $product->name . ' added to basket!']);
    }

    public function updateQty($id, $delta)
    {
        if (!isset($this->cart[$id])) return;
        
        $this->cart[$id]['qty'] += $delta;
        if ($this->cart[$id]['qty'] <= 0) {
            unset($this->cart[$id]);
        }
    }

    public function getTotalProperty()
    {
        return collect($this->cart)->sum(fn($item) => $item['price'] * $item['qty']);
    }

    public function checkout()
    {
        if (empty($this->cart)) return;

        // Logic to save order to database with table reference
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
        $products = Product::query()
            ->when($this->category !== 'All', fn($q) => $q->where('category', $this->category))
            ->get();

        return view('livewire.public-menu', [
            'products' => $products
        ])->layout('layouts.base'); // Uses the clean base layout without admin sidebar
    }
}