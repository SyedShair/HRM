<?php

namespace App\Http\Middleware;

use Closure;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Services\AuditService;
use App\Services\UserAgentParser;

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

            // FIX: this update was a no-op ("session live data not
            // storing") whenever no login_sessions row already existed
            // for the current session id - which happens if the Login
            // auth event never fires the way AppServiceProvider assumes
            // (e.g. an OTP-gated login flow that completes
            // authentication differently), or if the session id changes
            // after login (Laravel commonly regenerates it to prevent
            // session fixation), orphaning whatever row the Login
            // listener created under the old id. Rather than depending
            // on that event firing correctly, self-heal here: if the
            // update matches nothing, create the row instead of
            // silently doing nothing. This makes live-session tracking
            // work regardless of exactly how the login/OTP flow
            // completes, without needing to touch LoginController.
            $sessionId = $request->session()->getId();

            $updated = DB::table('login_sessions')
                ->where('session_id', $sessionId)
                ->whereNull('logout_at')
                ->update([
                    'last_activity_at' => now(),
                    'status'           => 'online',
                    'updated_at'       => now(),
                ]);

            if (!$updated) {
                $ua = UserAgentParser::parse($request->userAgent());

                DB::table('login_sessions')->insert([
                    'user_id'          => Auth::id(),
                    'session_id'       => $sessionId,
                    // Best-effort - the true login moment may have been
                    // slightly earlier if this is the self-heal path.
                    'login_at'         => now(),
                    'last_activity_at' => now(),
                    'ip_address'       => $request->ip(),
                    'user_agent'       => $request->userAgent(),
                    'browser'          => $ua['browser'],
                    'device'           => $ua['device'],
                    'status'           => 'online',
                    'created_at'       => now(),
                    'updated_at'       => now(),
                ]);
            }
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
