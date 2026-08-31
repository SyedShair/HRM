<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;

use Carbon\Carbon;

use App\Services\QueryAuditor;
use App\Services\AuditService;
use App\Services\UserAgentParser;

use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\Failed;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // Custom field validation rule
        Validator::extend('alpha_dash_space', function ($attribute, $value) {
            // Accept alpha, numbers, spaces, hyphens and underscores
            return preg_match('/^[\pL\d\s\-\_]+$/u', $value);
        });

        /*
        |--------------------------------------------------------------------------
        | Audit / Query Tracking
        |--------------------------------------------------------------------------
        */

        if (config('audit.enabled', true)) {

            DB::beforeExecuting(function ($sql, $bindings, $connection) {
                QueryAuditor::beforeExecuting(
                    $sql,
                    $bindings,
                    $connection
                );
            });

            DB::listen(function ($query) {
                QueryAuditor::afterExecuting($query);
            });

            /*
            |--------------------------------------------------------------------------
            | Authentication Events
            |--------------------------------------------------------------------------
            */

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
    }

    /**
     * Record successful login.
     *
     * IMPORTANT:
     * Do NOT create login_sessions here.
     *
     * TrackUserActivity should be the only writer of login_sessions
     * because the session ID can be regenerated immediately after login.
     */
    protected function recordAuditLogin(Login $event): void
    {
        try {
            AuditService::log([
                'action'      => 'login',
                'severity'    => 'success',
                'category'    => 'Authentication',
                'module'      => 'Authentication',
                'description' => 'User logged in',
            ]);
        } catch (\Throwable $e) {
            Log::warning(
                '[Audit] login tracking failed: ' .
                $e->getMessage()
            );
        }
    }

    /**
     * Record logout.
     */
    protected function recordAuditLogout(Logout $event): void
    {
        try {
            $tz = config('audit.timezone', 'Europe/London');

            $sessionId = request()->session()->getId();

            $session = DB::table('login_sessions')
                ->where('session_id', $sessionId)
                ->whereNull('logout_at')
                ->orderByDesc('id')
                ->first();

            /*
             * login_at comes from the database as a string.
             * Parse it explicitly before calculating the duration.
             */
            $duration = $session
                ? now($tz)->diffInSeconds(
                    Carbon::parse($session->login_at, $tz)
                )
                : null;

            DB::table('login_sessions')
                ->where('session_id', $sessionId)
                ->whereNull('logout_at')
                ->update([
                    'logout_at'  => now($tz),
                    'status'     => 'offline',
                    'updated_at' => now($tz),
                ]);

            AuditService::log([
                'user_id'     => $event->user->id ?? null,
                'action'      => 'logout',
                'severity'    => 'info',
                'category'    => 'Authentication',
                'module'      => 'Authentication',
                'description' => 'User logged out',
                'metadata'    => $duration !== null
                    ? [
                        'session_duration_seconds' => $duration,
                    ]
                    : null,
            ]);

        } catch (\Throwable $e) {
            Log::warning(
                '[Audit] logout tracking failed: ' .
                $e->getMessage()
            );
        }
    }

    /**
     * Record failed login attempt.
     */
    protected function recordAuditFailedLogin(Failed $event): void
    {
        try {
            $tz = config('audit.timezone', 'Europe/London');

            $ua = UserAgentParser::parse(
                request()->userAgent()
            );

            $username =
                $event->credentials['email']
                ?? $event->credentials['username']
                ?? null;

            DB::table('failed_logins')->insert([
                'username'       => $username,
                'ip_address'     => request()->ip(),
                'user_agent'     => request()->userAgent(),
                'browser'        => $ua['browser'],
                'device'         => $ua['device'],
                'failure_reason' => 'Invalid credentials',
                'attempted_at'   => now($tz),
                'created_at'     => now($tz),
                'updated_at'     => now($tz),
            ]);

            AuditService::log([
                'action'      => 'failed_login',
                'severity'    => 'warning',
                'category'    => 'Security',
                'module'      => 'Authentication',
                'description' => 'Failed login attempt for ' .
                    ($username ?? 'unknown'),
            ]);

        } catch (\Throwable $e) {
            Log::warning(
                '[Audit] failed-login tracking failed: ' .
                $e->getMessage()
            );
        }
    }
}