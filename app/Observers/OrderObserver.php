<?php

namespace App\Observers;

use App\Models\Order;
use App\Events\NewOrderReceived;
use App\Events\OrderStatusChanged;

class OrderObserver
{
    /**
     * Handle the Order "created" event.
     * Fires when new order is submitted (from frontend QR ordering)
     */
    public function created(Order $order)
    {
        // Only broadcast for QR orders
        if ($order->order_source === 'qr_scan') {
            // Load relationships for broadcasting
            $order->load(['table', 'items']);
            
            // Broadcast new order event
            broadcast(new NewOrderReceived($order))->toOthers();
        }
    }

    /**
     * Handle the Order "updated" event.
     * Fires when order status changes (from backend order management)
     */
    public function updated(Order $order)
    {
        // Check if status changed
        if ($order->isDirty('status')) {
            $oldStatus = $order->getOriginal('status');
            $newStatus = $order->status;
            
            // Load relationships
            $order->load(['table', 'items']);
            
            // Broadcast status change event
            broadcast(new OrderStatusChanged($order, $oldStatus, $newStatus))->toOthers();
        }
    }
}
