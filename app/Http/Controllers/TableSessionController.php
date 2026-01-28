<?php

namespace App\Http\Controllers;

use App\Models\Table;
use App\Models\TableSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;

class TableSessionController extends Controller
{
    // Cookie name for session token
    const SESSION_COOKIE = 'table_session_token';
    const SESSION_COOKIE_MINUTES = 240; // 4 hours

    /**
     * Handle QR code scan
     * GET /table/{qr_code}
     */
    public function scan(Request $request, string $qrCode)
    {
        // Find table by QR code
        $table = Table::with(['outlet.tenant', 'tableArea'])
                      ->byQrCode($qrCode)
                      ->active()
                      ->first();

        if (!$table) {
            return view('public.invalid', [
                'message' => 'QR Code tidak valid atau meja tidak aktif.'
            ]);
        }

        // Check if outlet is active
        if (!$table->outlet || !$table->outlet->is_active) {
            return view('public.invalid', [
                'message' => 'Outlet sedang tidak beroperasi.'
            ]);
        }

        // Get device fingerprint (simplified - you can use FingerprintJS for better accuracy)
        $fingerprint = $this->getDeviceFingerprint($request);

        // Check for existing session token in cookie
        $existingToken = $request->cookie(self::SESSION_COOKIE);
        
        if ($existingToken) {
            $existingSession = TableSession::byToken($existingToken)->active()->first();
            
            // If session exists and is for this table, resume it
            if ($existingSession && $existingSession->table_id === $table->id) {
                $existingSession->updateActivity();
                return redirect()->route('table.menu')
                    ->with('session_resumed', true);
            }
            
            // If session is for different table, check if we should switch
            if ($existingSession && $existingSession->table_id !== $table->id) {
                // You might want to handle this case - for now, we'll create new session
                // and let the old one expire naturally
            }
        }

        // Get or create session for this table
        $session = $table->getOrCreateSession($fingerprint);

        // Store session token in cookie
        $cookie = Cookie::make(
            self::SESSION_COOKIE,
            $session->session_token,
            self::SESSION_COOKIE_MINUTES,
            '/',
            null,
            false, // secure - set to true in production with HTTPS
            true   // httpOnly
        );

        return redirect()->route('table.menu')
            ->withCookie($cookie)
            ->with('session_started', true);
    }

    /**
     * Show menu page with table context
     * GET /menu
     */
    public function menu(Request $request)
    {
        $session = $this->getActiveSession($request);

        if (!$session) {
             return view('public.invalid', [
            'message' => 'Silakan scan QR code di meja Anda untuk mulai memesan.'
        ]);
        }

        // Update last activity
        $session->updateActivity();

        // Load relationships
        $session->load(['table.tableArea', 'outlet.tenant', 'orders' => function($q) {
            $q->latest()->take(5);
        }]);

        return view('public.menu', [
            'session' => $session,
            'table' => $session->table,
            'outlet' => $session->outlet,
            'tenant' => $session->tenant,
        ]);
    }

    /**
     * Get session info (for AJAX/Livewire)
     * GET /api/table/session
     */
    public function sessionInfo(Request $request)
    {
        $session = $this->getActiveSession($request);

        if (!$session) {
            return response()->json([
                'success' => false,
                'message' => 'No active session',
            ], 404);
        }

        $session->load(['table', 'outlet', 'orders']);

        return response()->json([
            'success' => true,
            'session' => [
                'id' => $session->id,
                'token' => $session->session_token,
                'status' => $session->status,
                'table' => [
                    'id' => $session->table->id,
                    'number' => $session->table->table_number,
                    'area' => $session->table->tableArea?->name,
                    'capacity' => $session->table->capacity,
                ],
                'outlet' => [
                    'id' => $session->outlet->id,
                    'name' => $session->outlet->name,
                ],
                'guest_count' => $session->guest_count,
                'started_at' => $session->started_at->toIso8601String(),
                'expires_at' => $session->expires_at->toIso8601String(),
                'duration' => $session->formatted_duration,
                'order_count' => $session->order_count,
                'total_amount' => $session->total_amount,
            ],
        ]);
    }

    /**
     * Update guest count
     * POST /api/table/guests
     */
    public function updateGuests(Request $request)
    {
        $request->validate([
            'guest_count' => 'required|integer|min:1|max:50',
        ]);

        $session = $this->getActiveSession($request);

        if (!$session) {
            return response()->json(['success' => false, 'message' => 'No active session'], 404);
        }

        $session->update(['guest_count' => $request->guest_count]);

        return response()->json([
            'success' => true,
            'guest_count' => $session->guest_count,
        ]);
    }

    /**
     * Request bill
     * POST /api/table/request-bill
     */
    public function requestBill(Request $request)
    {
        $session = $this->getActiveSession($request);

        if (!$session) {
            return response()->json(['success' => false, 'message' => 'No active session'], 404);
        }

        $session->update(['status' => 'billing']);

        // TODO: Notify staff via WebSocket/Pusher

        return response()->json([
            'success' => true,
            'message' => 'Permintaan tagihan telah dikirim ke pelayan.',
        ]);
    }

    /**
     * Call waiter
     * POST /api/table/call-waiter
     */
    public function callWaiter(Request $request)
    {
        $session = $this->getActiveSession($request);

        if (!$session) {
            return response()->json(['success' => false, 'message' => 'No active session'], 404);
        }

        // TODO: Notify staff via WebSocket/Pusher

        return response()->json([
            'success' => true,
            'message' => 'Pelayan akan segera datang ke meja Anda.',
        ]);
    }

    /**
     * End session (customer initiated)
     * POST /api/table/end-session
     */
    public function endSession(Request $request)
    {
        $session = $this->getActiveSession($request);

        if (!$session) {
            return response()->json(['success' => false, 'message' => 'No active session'], 404);
        }

        // Check if there are unpaid orders
        $unpaidOrders = $session->orders()->where('payment_status', 'unpaid')->exists();
        
        if ($unpaidOrders) {
            return response()->json([
                'success' => false,
                'message' => 'Masih ada pesanan yang belum dibayar.',
            ], 400);
        }

        $session->close();

        // Clear cookie
        $cookie = Cookie::forget(self::SESSION_COOKIE);

        return response()->json([
            'success' => true,
            'message' => 'Terima kasih atas kunjungan Anda!',
        ])->withCookie($cookie);
    }

    /**
     * Get active session from request
     */
    protected function getActiveSession(Request $request): ?TableSession
    {
        $token = $request->cookie(self::SESSION_COOKIE) 
                ?? $request->header('X-Table-Session')
                ?? $request->input('session_token');

        if (!$token) {
            return null;
        }

        return TableSession::byToken($token)->active()->first();
    }

    /**
     * Generate device fingerprint
     */
    protected function getDeviceFingerprint(Request $request): string
    {
        return md5(implode('|', [
            $request->ip(),
            $request->userAgent(),
            $request->header('Accept-Language'),
        ]));
    }
}