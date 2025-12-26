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

    /**
     * Captures the table ID from the URL parameter defined in routes.
     * Example: /order/A1 sets $tableId to 'A1'
     */
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
        // Emit notification for the front-end feedback
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

    /**
     * Finalizes the order and saves it to the database with the table reference.
     */
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
        $products = Product::query()
            ->when($this->category !== 'All', fn($q) => $q->where('category', $this->category))
            ->get();

        return view('livewire.public-menu', [
            'products' => $products
        ])->layout('layouts.base');
    }
}