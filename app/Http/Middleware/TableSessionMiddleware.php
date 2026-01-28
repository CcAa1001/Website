<?php

namespace App\Http\Middleware;

use App\Models\TableSession;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TableSessionMiddleware
{
    const SESSION_COOKIE = 'table_session_token';

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->cookie(self::SESSION_COOKIE)
                ?? $request->header('X-Table-Session')
                ?? $request->input('session_token');

        if (!$token) {
            return $this->noSessionResponse($request);
        }

        $session = TableSession::byToken($token)
                              ->active()
                              ->with(['table', 'outlet', 'tenant'])
                              ->first();

        if (!$session) {
            return $this->noSessionResponse($request);
        }

        // Check if session is expired
        if ($session->isExpired()) {
            $session->update(['status' => 'expired']);
            return $this->expiredSessionResponse($request);
        }

        // Update last activity
        $session->last_activity_at = now();
        $session->save();

        // Share session data with all views and request
        $request->merge(['table_session' => $session]);
        view()->share('tableSession', $session);
        view()->share('currentTable', $session->table);
        view()->share('currentOutlet', $session->outlet);
        view()->share('currentTenant', $session->tenant);

        return $next($request);
    }

    /**
     * Handle no session response
     */
    protected function noSessionResponse(Request $request): Response
    {
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => false,
                'error' => 'no_session',
                'message' => 'Silakan scan QR code di meja Anda untuk mulai memesan.',
            ], 401);
        }

        return redirect()->route('home')
            ->with('error', 'Silakan scan QR code di meja Anda untuk mulai memesan.');
    }

    /**
     * Handle expired session response
     */
    protected function expiredSessionResponse(Request $request): Response
    {
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => false,
                'error' => 'session_expired',
                'message' => 'Sesi Anda telah berakhir. Silakan scan QR code untuk memulai sesi baru.',
            ], 401);
        }

        return redirect()->route('home')
            ->with('error', 'Sesi Anda telah berakhir. Silakan scan QR code untuk memulai sesi baru.');
    }
}
