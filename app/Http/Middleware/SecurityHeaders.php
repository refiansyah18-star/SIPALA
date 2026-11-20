<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * Tambahkan HTTP Security Headers standar produksi pada seluruh respons web.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');

        // Cegah kebocoran token reset password melalui header Referer
        if ($request->is('alumni/reset-password*')) {
            $response->headers->set('Referrer-Policy', 'no-referrer');
        } else {
            $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        }

        return $response;
    }
}
