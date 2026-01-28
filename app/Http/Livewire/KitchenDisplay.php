<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;

class KitchenDisplay extends Component
{
    public $selectedStation = 'all';
    public $autoRefresh = true;
    public $soundEnabled = true;
    
    // Tracks last order count for new order alerts
    public $lastOrderCount = 0;

    protected $listeners = ['refreshOrders' => '$refresh'];

    public function mount()
    {
        $this->lastOrderCount = $this->getPendingOrdersCount();
    }

    public function render()
    {
        $user = auth()->user();

        // Get orders that need kitchen attention
        $orders = Order::where('tenant_id', $user->tenant_id)
            ->where('outlet_id', $user->outlet_id)
            ->whereIn('status', ['confirmed', 'preparing'])
            ->with(['table', 'items' => function($query) {
                $query->whereIn('kitchen_status', ['pending', 'preparing'])
                      ->orderBy('created_at');
            }])
            ->orderBy('created_at')
            ->get();

        // Group by table for better organization
        $ordersByTable = $orders->groupBy('table_id');

        // Check for new orders
        $currentCount = $this->getPendingOrdersCount();
        $hasNewOrders = $currentCount > $this->lastOrderCount;
        $this->lastOrderCount = $currentCount;

        return view('livewire.kitchen-display', [
            'orders' => $orders,
            'ordersByTable' => $ordersByTable,
            'hasNewOrders' => $hasNewOrders,
            'stats' => [
                'pending' => OrderItem::whereHas('order', function($q) use ($user) {
                    $q->where('tenant_id', $user->tenant_id)
                      ->where('outlet_id', $user->outlet_id)
                      ->whereIn('status', ['confirmed', 'preparing']);
                })->where('kitchen_status', 'pending')->count(),
                
                'preparing' => OrderItem::whereHas('order', function($q) use ($user) {
                    $q->where('tenant_id', $user->tenant_id)
                      ->where('outlet_id', $user->outlet_id)
                      ->where('status', 'preparing');
                })->where('kitchen_status', 'preparing')->count(),
                
                'orders_count' => $orders->count(),
            ]
        ])->layout('layouts.kitchen');
    }

    public function startItem($itemId)
    {
        $item = OrderItem::findOrFail($itemId);
        $item->update([
            'kitchen_status' => 'preparing',
            'kitchen_printed_at' => now(),
        ]);

        // Update order status if it's still confirmed
        if ($item->order->status === 'confirmed') {
            $item->order->update(['status' => 'preparing']);
        }

        $this->dispatch('item-updated');
    }

    public function completeItem($itemId)
    {
        $item = OrderItem::findOrFail($itemId);
        $item->update([
            'kitchen_status' => 'ready',
            'prepared_at' => now(),
        ]);

        // Check if all items in order are ready
        $order = $item->order;
        $allReady = $order->items()->where('kitchen_status', '!=', 'ready')->count() === 0;
        
        if ($allReady) {
            $order->update([
                'status' => 'ready',
                'prepared_at' => now(),
            ]);
        }

        $this->dispatch('item-updated');
    }

    public function completeOrder($orderId)
    {
        $order = Order::findOrFail($orderId);
        
        // Mark all items as ready
        $order->items()->update([
            'kitchen_status' => 'ready',
            'prepared_at' => now(),
        ]);

        // Update order
        $order->update([
            'status' => 'ready',
            'prepared_at' => now(),
        ]);

        session()->flash('message', 'Order marked as ready!');
    }

    public function toggleAutoRefresh()
    {
        $this->autoRefresh = !$this->autoRefresh;
    }

    public function toggleSound()
    {
        $this->soundEnabled = !$this->soundEnabled;
    }

    protected function getPendingOrdersCount()
    {
        $user = auth()->user();
        return Order::where('tenant_id', $user->tenant_id)
            ->where('outlet_id', $user->outlet_id)
            ->whereIn('status', ['confirmed', 'preparing'])
            ->count();
    }

    public function getElapsedTime($timestamp)
    {
        if (!$timestamp) return '';
        
        $minutes = now()->diffInMinutes($timestamp);
        
        if ($minutes < 60) {
            return $minutes . 'm';
        }
        
        $hours = floor($minutes / 60);
        $mins = $minutes % 60;
        return $hours . 'h ' . $mins . 'm';
    }

    public function getUrgencyClass($timestamp)
    {
        if (!$timestamp) return '';
        
        $minutes = now()->diffInMinutes($timestamp);
        
        if ($minutes > 30) return 'urgent';
        if ($minutes > 15) return 'warning';
        return '';
    }
}