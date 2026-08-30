<?php

namespace App\Http\Middleware;

use Closure;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Services\AuditService;

/**
 * Registered once on the 'web' middleware group in bootstrap/app.php -
 * no existing route definitions need to change. No-ops entirely for
 * guests and for static-asset-like paths (see config/audit.php's
 * excluded_request_patterns).
 */
class TrackUserActivity
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        if (!config('audit.enabled', true) || !Auth::check()) {
            return $response;
        }

        try {
            if ($this->isExcluded($request)) {
                return $response;
            }

            AuditService::log([
                'action'      => 'request',
                'severity'    => 'info',
                'category'    => 'Other',
                'module'      => optional($request->route())->getName(),
                'description' => 'Page request: '.$request->method().' '.$request->path(),
            ]);

            DB::table('login_sessions')
                ->where('session_id', $request->session()->getId())
                ->whereNull('logout_at')
                ->update([
                    'last_activity_at' => now(),
                    'status'           => 'online',
                    'updated_at'       => now(),
                ]);
        } catch (\Throwable $e) {
            // Fail-safe: tracking must never break the real request.
            Log::warning('[Audit] request tracking failed: '.$e->getMessage());
        }

        return $response;
    }

    protected function isExcluded(Request $request): bool
    {
        $path = $request->path();

        foreach (config('audit.excluded_request_patterns', []) as $pattern) {
            if (fnmatch($pattern, $path)) {
                return true;
            }
        }

        return false;
    }
}
