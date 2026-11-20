<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * Check if the authenticated user has one of the allowed roles.
     * Usage: ->middleware('role:ADMIN') or ->middleware('role:ADMIN,STAFF')
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (!$user || !$user->is_active) {
            if ($user) {
                \Illuminate\Support\Facades\Auth::guard('web')->logout();
                if ($request->hasSession()) {
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();
                }
            }
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthorized.'], 403);
            }
            return redirect()->route('login')->withErrors([
                'login' => 'Akun Anda tidak aktif atau akses ditolak.',
            ]);
        }

        $userRole = $request->user()->userType->name;

        // SUPER_ADMIN (Pustikom) dapat mengakses seluruh halaman manajemen (ADMIN, SUPER_ADMIN, STAFF),
        // namun bukan halaman pengajuan layanan pemohon (end-user)
        if ($userRole === \App\Models\UserType::SUPER_ADMIN) {
            $managementRoles = [\App\Models\UserType::SUPER_ADMIN, \App\Models\UserType::ADMIN, \App\Models\UserType::STAFF];
            if (!empty(array_intersect($roles, $managementRoles))) {
                return $next($request);
            }
            return redirect()->route('dashboard')->with('info', 'Akun Super Admin (Pustikom) dikhususkan untuk pengelolaan sistem dan maintenance, bukan untuk mengajukan layanan.');
        }

        if (!in_array($userRole, $roles)) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Anda tidak memiliki akses ke halaman ini.'], 403);
            }
            abort(403, 'Anda tidak memiliki akses ke halaman ini.');
        }

        return $next($request);
    }
}
