<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();

        // Super admin has access to everything
        if ($user->role && strtolower($user->role->name) === 'admin') {
            return $next($request);
        }

        // Check if user has required role
        if ($user->role && in_array(strtolower($user->role->name), array_map('strtolower', $roles))) {
            return $next($request);
        }

        // Access denied
        abort(403, 'Unauthorized access. Required role: ' . implode(', ', $roles));
    }
}
