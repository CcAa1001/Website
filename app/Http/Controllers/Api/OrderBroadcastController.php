<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Events\NewOrderReceived;
use Illuminate\Http\Request;

class OrderBroadcastController extends Controller
{
    /**
     * Receive notification from frontend and broadcast
     */
    public function broadcastNewOrder(Request $request)
    {
        $orderId = $request->input('order_id');
        
        if (!$orderId) {
            return response()->json(['error' => 'Order ID required'], 400);
        }

        // Load order with relationships
        $order = Order::with(['table', 'items'])->find($orderId);
        
        if (!$order) {
            return response()->json(['error' => 'Order not found'], 404);
        }

        // Only broadcast QR orders
        if ($order->order_source === 'qr_scan') {
            broadcast(new NewOrderReceived($order))->toOthers();
            
            \Log::info('✅ Broadcast triggered via API', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Order broadcasted',
                'order_number' => $order->order_number,
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Order source not qr_scan',
        ]);
    }
}