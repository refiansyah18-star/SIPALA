<?php

namespace App\Http\Middleware;

use App\Models\ActivityLog;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LogActivity
{
    /**
     * Log admin/staff activity on important actions.
     *
     * This middleware logs POST/PUT/PATCH/DELETE requests.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Only log state-changing requests from admin/staff
        if (in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'])) {
            $user = $request->user();

            if ($user && ($user->isAdmin() || $user->isStaff())) {
                $action = match ($request->method()) {
                    'POST' => ActivityLog::ACTION_CREATE,
                    'PUT', 'PATCH' => ActivityLog::ACTION_UPDATE,
                    'DELETE' => ActivityLog::ACTION_DELETE,
                    default => 'UNKNOWN',
                };

                // Extract module from route name (e.g., admin.categories.store -> CATEGORY)
                $routeName = $request->route()?->getName() ?? '';
                $module = $this->extractModule($routeName);

                ActivityLog::log(
                    userId: $user->id,
                    action: $action,
                    module: $module,
                    description: "{$request->method()} {$request->path()}"
                );
            }
        }

        return $response;
    }

    /**
     * Extract module name from route name.
     */
    private function extractModule(string $routeName): string
    {
        $segments = explode('.', $routeName);

        // Look for known module names in route segments
        $moduleMap = [
            'categories' => 'CATEGORY',
            'services' => 'SERVICE',
            'tickets' => 'TICKET',
            'users' => 'USER',
            'staff' => 'STAFF',
            'statuses' => 'STATUS',
            'units' => 'UNIT',
            'prodi' => 'PRODI',
            'alumni' => 'ALUMNI',
            'assignments' => 'TICKET',
            'messages' => 'TICKET',
        ];

        foreach ($segments as $segment) {
            if (isset($moduleMap[$segment])) {
                return $moduleMap[$segment];
            }
        }

        return strtoupper($segments[1] ?? $segments[0] ?? 'UNKNOWN');
    }
}
