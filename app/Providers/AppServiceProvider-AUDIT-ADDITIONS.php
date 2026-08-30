<?php

/*
|--------------------------------------------------------------------------
| THIS IS NOT A STANDALONE FILE
|--------------------------------------------------------------------------
|
| Your app already has app/Providers/AppServiceProvider.php with its own
| boot() method - I don't have its current content, so rather than
| overwrite it blindly, copy the pieces below INTO your existing
| boot() method (and add the three protected methods below your
| existing methods, and the four `use` imports below your existing
| imports). Nothing here should replace anything already in that file.
|
| WHAT THIS REGISTERS:
|  1. DB::beforeExecuting() / DB::listen() - routes every query through
|     QueryAuditor, which is what actually builds INSERT/UPDATE/DELETE
|     audit rows. See QueryAuditor's class docblock for exactly what it
|     can and cannot reliably capture.
|  2. Login/Logout/Failed auth event listeners - these fire from
|     Laravel's built-in Auth facade automatically; nothing in your
|     LoginController needs to change for these to work, AS LONG AS your
|     login flow calls Auth::attempt()/Auth::login() (the standard
|     Laravel way) rather than manually setting the session. If your
|     LoginController does something custom, tell me and I'll adjust
|     this to match.
|
|--------------------------------------------------------------------------
*/

// ---- add these four `use` statements to the top of AppServiceProvider.php ----
use DB;
use App\Services\QueryAuditor;
use App\Services\AuditService;
use App\Services\UserAgentParser;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\Failed;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;

// ---- add this INSIDE your existing boot() method ----
if (config('audit.enabled', true)) {

    DB::beforeExecuting(function ($sql, $bindings, $connection) {
        QueryAuditor::beforeExecuting($sql, $bindings, $connection);
    });

    DB::listen(function ($query) {
        QueryAuditor::afterExecuting($query);
    });

    Event::listen(Login::class, function (Login $event) {
        $this->recordAuditLogin($event);
    });

    Event::listen(Logout::class, function (Logout $event) {
        $this->recordAuditLogout($event);
    });

    Event::listen(Failed::class, function (Failed $event) {
        $this->recordAuditFailedLogin($event);
    });
}

// ---- add these three methods anywhere in the AppServiceProvider class ----

protected function recordAuditLogin(Login $event): void
{
    try {
        $user = $event->user;
        $sessionId = request()->session()->getId();
        $ua = UserAgentParser::parse(request()->userAgent());

        DB::table('login_sessions')->insert([
            'user_id'          => $user->id,
            'session_id'       => $sessionId,
            'login_at'         => now(),
            'last_activity_at' => now(),
            'ip_address'       => request()->ip(),
            'user_agent'       => request()->userAgent(),
            'browser'          => $ua['browser'],
            'device'           => $ua['device'],
            'status'           => 'online',
            'created_at'       => now(),
            'updated_at'       => now(),
        ]);

        AuditService::log([
            'action'      => 'login',
            'severity'    => 'success',
            'category'    => 'Authentication',
            'module'      => 'Authentication',
            'description' => 'User logged in',
            'metadata'    => ['session_id' => $sessionId],
        ]);
    } catch (\Throwable $e) {
        Log::warning('[Audit] login tracking failed: '.$e->getMessage());
    }
}

protected function recordAuditLogout(Logout $event): void
{
    try {
        $sessionId = request()->session()->getId();

        $session = DB::table('login_sessions')
            ->where('session_id', $sessionId)
            ->whereNull('logout_at')
            ->orderByDesc('id')
            ->first();

        $duration = $session ? now()->diffInSeconds($session->login_at) : null;

        DB::table('login_sessions')
            ->where('session_id', $sessionId)
            ->whereNull('logout_at')
            ->update([
                'logout_at'  => now(),
                'status'     => 'offline',
                'updated_at' => now(),
            ]);

        AuditService::log([
            'user_id'     => $event->user->id ?? null,
            'action'      => 'logout',
            'severity'    => 'info',
            'category'    => 'Authentication',
            'module'      => 'Authentication',
            'description' => 'User logged out',
            'metadata'    => $duration !== null ? ['session_duration_seconds' => $duration] : null,
        ]);
    } catch (\Throwable $e) {
        Log::warning('[Audit] logout tracking failed: '.$e->getMessage());
    }
}

protected function recordAuditFailedLogin(Failed $event): void
{
    try {
        $ua = UserAgentParser::parse(request()->userAgent());
        $username = $event->credentials['email'] ?? $event->credentials['username'] ?? null;

        DB::table('failed_logins')->insert([
            'username'       => $username,
            'ip_address'     => request()->ip(),
            'user_agent'     => request()->userAgent(),
            'browser'        => $ua['browser'],
            'device'         => $ua['device'],
            'failure_reason' => 'Invalid credentials',
            'attempted_at'   => now(),
            'created_at'     => now(),
            'updated_at'     => now(),
        ]);

        AuditService::log([
            'action'      => 'failed_login',
            'severity'    => 'warning',
            'category'    => 'Security',
            'module'      => 'Authentication',
            'description' => 'Failed login attempt for '.($username ?? 'unknown'),
        ]);
    } catch (\Throwable $e) {
        Log::warning('[Audit] failed-login tracking failed: '.$e->getMessage());
    }
}
