<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;

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
            // This will only accept alpha, numbers, spaces, hyphens and underscores
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
     */
    protected function recordAuditLogin(Login $event): void
    {
        try {
            $user = $event->user;

            $sessionId = request()->session()->getId();

            $ua = UserAgentParser::parse(
                request()->userAgent()
            );

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
                'metadata'    => [
                    'session_id' => $sessionId,
                ],
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
            $sessionId = request()->session()->getId();

            $session = DB::table('login_sessions')
                ->where('session_id', $sessionId)
                ->whereNull('logout_at')
                ->orderByDesc('id')
                ->first();

            $duration = $session
                ? now()->diffInSeconds($session->login_at)
                : null;

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
                'attempted_at'   => now(),
                'created_at'     => now(),
                'updated_at'     => now(),
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