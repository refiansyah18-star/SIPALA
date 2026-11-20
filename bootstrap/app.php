<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->appendToGroup('web', \App\Http\Middleware\SecurityHeaders::class);

        $middleware->alias([
            'active' => \App\Http\Middleware\EnsureUserIsActive::class,
            'role' => \App\Http\Middleware\RoleMiddleware::class,
            'log.activity' => \App\Http\Middleware\LogActivity::class,
            'prevent.back' => \App\Http\Middleware\PreventBackHistory::class,
        ]);

        // Proteksi CSRF penuh aktif untuk semua form termasuk logout
        $middleware->validateCsrfTokens(except: [
            // No exceptions
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $handleCsrfMismatch = function ($request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Sesi halaman telah berakhir. Silakan muat ulang halaman.',
                ], 419);
            }

            // Jika 419 terjadi saat klik tombol Logout (misal sesi sudah habis atau logout dari tab lain),
            // langsung bersihkan sesi dan arahkan ke halaman login tanpa error.
            if ($request->is('logout')) {
                \Illuminate\Support\Facades\Auth::logout();
                if ($request->hasSession()) {
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();
                }

                return redirect()->route('login');
            }

            if ($request->hasSession()) {
                $request->session()->regenerateToken();
            }

            $previousUrl = url()->previous();
            $previousHost = $previousUrl ? parse_url($previousUrl, PHP_URL_HOST) : null;
            $isSameHost = $previousHost && strcasecmp($previousHost, $request->getHost()) === 0;
            $targetUrl = ($isSameHost && $previousUrl !== $request->fullUrl())
                ? $previousUrl
                : route('login');

            return redirect()->to($targetUrl)
                ->withInput($request->except(['password', 'password_confirmation', 'current_password', '_token']))
                ->withErrors([
                    'login' => 'Sesi halaman sempat diperbarui (karena pergantian tab/akun). Silakan kirim ulang.',
                ]);
        };

        $exceptions->render(function (\Illuminate\Session\TokenMismatchException $e, $request) use ($handleCsrfMismatch) {
            return $handleCsrfMismatch($request);
        });

        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\HttpException $e, $request) use ($handleCsrfMismatch) {
            $statusCode = $e->getStatusCode();

            // Laravel 11/12 mengonversi TokenMismatchException menjadi HttpException(419) sebelum render callback
            if ($statusCode === 419 || $e->getPrevious() instanceof \Illuminate\Session\TokenMismatchException) {
                return $handleCsrfMismatch($request);
            }

            $viewPath = "errors.{$statusCode}";
            if (view()->exists($viewPath)) {
                return response()->view($viewPath, [], $statusCode);
            }
        });
    })->create();
