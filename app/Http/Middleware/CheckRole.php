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
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();
        
        // Ambil data role user
        $userRoleName = strtolower($user->role->name ?? '');
        $userRoleSlug = strtolower($user->role->slug ?? '');

        // Normalisasi daftar role yang diizinkan (dari parameter route)
        $allowedRoles = array_map('strtolower', $roles);

        // 1. Cek Super Admin (Bypass segalanya)
        if ($userRoleName === 'super admin' || $userRoleSlug === 'super_admin' || $userRoleName === 'admin') {
            return $next($request);
        }

        // 2. Cek Role Spesifik (Cek Name ATAU Slug)
        // Jika salah satu cocok, izinkan akses
        if (in_array($userRoleName, $allowedRoles) || in_array($userRoleSlug, $allowedRoles)) {
            return $next($request);
        }

        // Jika gagal, tampilkan error
        abort(403, 'Unauthorized access. Required role: ' . implode(', ', $roles));
    }
}