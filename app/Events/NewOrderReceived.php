<?php

namespace App\Events;

use App\Models\Order;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewOrderReceived implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $order;
    public $outletId;

    /**
     * Create a new event instance.
     */
    public function __construct(Order $order)
    {
        $this->order = $order->load(['table', 'items']);
        $this->outletId = $order->outlet_id;
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('kitchen.' . $this->outletId),
            new PrivateChannel('dashboard.' . $this->outletId),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'order.new';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'order_id' => $this->order->id,
            'order_number' => $this->order->order_number,
            'table_number' => $this->order->table?->table_number,
            'table_id' => $this->order->table_id,
            'items' => $this->order->items->map(fn($item) => [
                'id' => $item->id,
                'product_name' => $item->product_name,
                'variant_name' => $item->variant_name,
                'quantity' => $item->quantity,
                'kitchen_status' => $item->kitchen_status,
            ]),
            'grand_total' => $this->order->grand_total,
            'order_source' => $this->order->order_source,
            'created_at' => $this->order->created_at->toIso8601String(),
        ];
    }
}
