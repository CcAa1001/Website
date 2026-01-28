<?php

namespace App\Events;

use App\Models\TableSession;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Observers\OrderObserver;
use App\Observers\TableSessionObserver;
use App\Providers\TableSessionProvider;

class TableSessionEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $session;
    public $action; // 'created', 'updated', 'closed'
    public $outletId;

    /**
     * Create a new event instance.
     */
    public function __construct(TableSession $session, string $action)
    {
        $this->session = $session->load(['table']);
        $this->action = $action;
        $this->outletId = $session->outlet_id;
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('dashboard.' . $this->outletId),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'session.' . $this->action;
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'session_id' => $this->session->id,
            'table_id' => $this->session->table_id,
            'table_number' => $this->session->table->table_number,
            'status' => $this->session->status,
            'guest_count' => $this->session->guest_count,
            'started_at' => $this->session->started_at->toIso8601String(),
            'order_count' => $this->session->order_count,
            'total_amount' => $this->session->total_amount,
            'action' => $this->action,
        ];
    }
}
