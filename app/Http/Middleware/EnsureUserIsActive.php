<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsActive
{
    /**
     * Pastikan akun yang sedang login masih aktif.
     * Jika dinonaktifkan oleh Admin saat sesi berjalan, segera keluarkan (logout) dan invalidasi sesi.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && !$user->is_active) {
            Auth::guard('web')->logout();
            if ($request->hasSession()) {
                $request->session()->invalidate();
                $request->session()->regenerateToken();
            }

            if ($request->expectsJson()) {
                return response()->json(['message' => 'Akun Anda telah dinonaktifkan oleh Administrator.'], 403);
            }

            return redirect()->route('login')->withErrors([
                'login' => 'Akun Anda telah dinonaktifkan oleh Administrator.',
            ]);
        }

        return $next($request);
    }
}
