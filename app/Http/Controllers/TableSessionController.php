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
     * GET /table/{code}
     */
    public function scan(Request $request, string $code)
    {
        // 1. Logika Pencarian yang Valid (Support UUID & QR Biasa)
        $isUuid = preg_match('/^[a-f\d]{8}-(?:[a-f\d]{4}-){3}[a-f\d]{12}$/i', $code);

        // Mulai query dengan eager loading
        $query = Table::with(['outlet.tenant', 'tableArea'])->active();

        if ($isUuid) {
            // Jika format UUID, cari berdasarkan ID atau QR Code
            $query->where(function($q) use ($code) {
                $q->where('id', $code)
                  ->orWhere('qr_code', $code);
            });
        } else {
            // Jika format biasa, cari berdasarkan scope QR Code
            // Pastikan scope 'byQrCode' ada di Model Table, atau gunakan ->where('qr_code', $code)
            $query->where('qr_code', $code);
        }

        $table = $query->first();

        // 2. Validasi Jika Meja Tidak Ditemukan
        if (!$table) {
            return view('public.invalid', [
                'message' => 'QR Code tidak valid atau meja tidak aktif.'
            ]);
        }

        // 3. Check if outlet is active
        if (!$table->outlet || !$table->outlet->is_active) {
            return view('public.invalid', [
                'message' => 'Outlet sedang tidak beroperasi.'
            ]);
        }

        // Get device fingerprint
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
        }

        // 4. Get or create session for this table (PENTING: Ini membuat Token)
        $session = $table->getOrCreateSession($fingerprint);

        // 5. Simpan Token ke dalam Cookie Browser
        $cookie = Cookie::make(
            self::SESSION_COOKIE,
            $session->session_token,
            self::SESSION_COOKIE_MINUTES,
            '/',
            null,
            false, 
            true
        );

        // 6. Redirect ke Menu dengan membawa Cookie
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
        // Fungsi ini mencari Cookie yang dibuat oleh fungsi scan() di atas
        $session = $this->getActiveSession($request);

        if (!$session) {
             return view('public.invalid', [
            'message' => 'Sesi tidak valid. Silakan scan QR code di meja Anda untuk mulai memesan.'
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