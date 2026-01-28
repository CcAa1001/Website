<?php

namespace App\Observers;

use App\Events\TableSessionEvent;
use App\Models\TableSession;

class TableSessionObserver
{
    /**
     * Handle the TableSession "created" event.
     */
    public function created(TableSession $session): void
    {
        // Only broadcast for active sessions
        if (in_array($session->status, ['active', 'ordering'])) {
            broadcast(new TableSessionEvent($session, 'created'))->toOthers();
        }
    }

    /**
     * Handle the TableSession "updated" event.
     */
    public function updated(TableSession $session): void
    {
        // Broadcast session updates
        if (in_array($session->status, ['active', 'ordering', 'billing'])) {
            broadcast(new TableSessionEvent($session, 'updated'))->toOthers();
        }
        
        // Broadcast when session is closed
        if ($session->status === 'closed') {
            broadcast(new TableSessionEvent($session, 'closed'))->toOthers();
        }
    }
}
