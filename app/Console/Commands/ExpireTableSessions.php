<?php
namespace App\Console\Commands;
use Illuminate\Console\Command;
use App\Models\TableSession;
class ExpireTableSessions extends Command
{
    protected $signature = 'tablesessions:expire';
    protected $description = 'Expire table sessions that have passed their expiration time';
public function handle()
{
    $expiredSessions = TableSession::whereIn('status', ['active', 'ordering', 'billing'])
        ->where('expires_at', '<', now())
        ->with('table')
        ->get();

    foreach ($expiredSessions as $session) {
        $session->update(['status' => 'expired']);
        
        // Reset the table only if it has no OTHER active session
        if ($session->table && !$session->table->hasActiveSession()) {
            $session->table->update(['status' => 'available', 'current_order_id' => null]);
        }
    }

    $this->info("Expired {$expiredSessions->count()} sessions.");
}}