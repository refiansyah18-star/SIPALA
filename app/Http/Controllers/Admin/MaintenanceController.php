<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class MaintenanceController extends Controller
{
    /**
     * Show System & Maintenance Dashboard for Super Admin (Pustikom).
     */
    public function index()
    {
        // System Information
        $systemInfo = [
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'app_env' => config('app.env'),
            'app_debug' => config('app.debug') ? 'Enabled (ON)' : 'Disabled (OFF)',
            'timezone' => config('app.timezone'),
            'is_maintenance' => app()->isDownForMaintenance(),
            'db_connection' => config('database.default'),
            'server_os' => PHP_OS_FAMILY,
        ];

        // Database status & count
        $dbStatus = 'Connected';
        try {
            DB::connection()->getPdo();
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Database connection check failed: ' . $e->getMessage());
            $dbStatus = 'Disconnected';
        }

        $counts = [
            'total_users' => User::count(),
            'total_tickets' => Ticket::count(),
            'total_logs' => ActivityLog::count(),
        ];

        // Recent activity logs
        $recentLogs = ActivityLog::with('user.userType')
            ->latest('created_at')
            ->take(8)
            ->get();

        return view('admin.maintenance.index', compact('systemInfo', 'dbStatus', 'counts', 'recentLogs'));
    }

    /**
     * Toggle Maintenance Mode (Pustikom maintenance).
     */
    public function toggleMaintenance(Request $request)
    {
        if (app()->isDownForMaintenance()) {
            Artisan::call('up');
            ActivityLog::log(
                auth()->id(),
                ActivityLog::ACTION_UPDATE,
                'SYSTEM',
                null,
                'Super Admin mengaktifkan kembali sistem (Maintenance Mode OFF)'
            );
            return redirect()->route('admin.maintenance.index')
                ->with('success', 'Sistem berhasil diaktifkan kembali (Maintenance Mode OFF).');
        } else {
            $secret = config('app.maintenance.secret') ?: env('MAINTENANCE_SECRET');
            if (empty($secret)) {
                $secret = hash('sha256', (string) (config('app.key') . '-sipala-maintenance-bypass'));
            }

            // Catat log dulu sebelum sistem masuk mode maintenance
            ActivityLog::log(
                auth()->id(),
                ActivityLog::ACTION_UPDATE,
                'SYSTEM',
                null,
                'Super Admin mengaktifkan Maintenance Mode'
            );

            Artisan::call('down', [
                '--secret' => $secret,
                '--render' => 'errors::503',
            ]);

            // Berikan cookie bypass langsung ke browser Super Admin yang mengaktifkan
            return redirect()->route('admin.maintenance.index')
                ->withCookie(\Illuminate\Foundation\Http\MaintenanceModeBypassCookie::create($secret))
                ->with('success', 'Maintenance Mode berhasil diaktifkan. Pengguna biasa kini dialihkan ke halaman pemeliharaan.');
        }
    }

    /**
     * Clear application cache, views, and routes.
     */
    public function clearCache()
    {
        Artisan::call('cache:clear');
        Artisan::call('view:clear');
        Artisan::call('route:clear');
        Artisan::call('config:clear');

        ActivityLog::log(
            auth()->id(),
            ActivityLog::ACTION_DELETE,
            'SYSTEM',
            null,
            'Super Admin membersihkan cache sistem (cache, view, route, config)'
        );

        return redirect()->route('admin.maintenance.index')
            ->with('success', 'Seluruh cache sistem (cache, view, route, config) berhasil dibersihkan.');
    }
}
