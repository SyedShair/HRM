<?php

namespace App\Services;

use DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * Single write path for every audit row, used by QueryAuditor,
 * TrackUserActivity, and the auth event listeners in
 * AppServiceProvider. Centralising the insert here means "never store
 * a password", "always fail safe", and "always fill in user/IP/browser
 * defaults" only need to be correct in one place.
 */
class AuditService
{
    /**
     * Write one row to activity_logs. Never throws - a logging failure
     * must never break the real request (per the fail-safe
     * requirement), so any exception here is swallowed and written to
     * the normal Laravel log instead.
     */
    public static function log(array $attributes): void
    {
        if (!config('audit.enabled', true)) {
            return;
        }

        try {
            $user = Auth::user();
            $request = request();
            $ua = UserAgentParser::parse($request?->userAgent());

            $defaults = [
                'user_id'          => $user->id ?? null,
                'user_name'        => $user->name ?? null,
                // This app's existing users table uses acc_type as its
                // role field (see table::users()'s select list) -
                // reused here rather than inventing a parallel concept.
                'role'             => $user->acc_type ?? null,
                'category'         => 'Other',
                'action'           => 'other',
                'severity'         => 'info',
                'module'           => null,
                'table_name'       => null,
                'record_id'        => null,
                'description'      => null,
                'url'              => $request?->fullUrl(),
                'route_name'       => optional($request?->route())->getName(),
                'http_method'      => $request?->method(),
                'ip_address'       => $request?->ip(),
                'user_agent'       => $request?->userAgent(),
                'browser'          => $ua['browser'],
                'browser_version'  => $ua['browser_version'],
                'operating_system' => $ua['os'],
                'device'           => $ua['device'],
                'old_data'         => null,
                'new_data'         => null,
                'metadata'         => null,
                'created_at'       => now(),
                'updated_at'       => now(),
            ];

            $row = array_merge($defaults, $attributes);

            foreach (['old_data', 'new_data', 'metadata'] as $field) {
                if (is_array($row[$field])) {
                    $row[$field] = json_encode(self::maskSensitive($row[$field]), JSON_UNESCAPED_UNICODE);
                }
            }

            DB::table('activity_logs')->insert($row);
        } catch (\Throwable $e) {
            // Deliberately swallowed past this point - see class
            // docblock. The calling request must continue normally.
            Log::warning('[Audit] failed to write activity log: '.$e->getMessage());
        }
    }

    /**
     * Recursively replaces any configured sensitive field with a mask.
     * Applied to every old/new data payload before it's persisted.
     */
    public static function maskSensitive(array $data): array
    {
        $sensitive = array_map('strtolower', config('audit.sensitive_fields', []));

        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $data[$key] = self::maskSensitive($value);
                continue;
            }

            if (in_array(strtolower((string) $key), $sensitive, true)) {
                $data[$key] = '********';
            }
        }

        return $data;
    }

    public static function category(?string $table): string
    {
        if (!$table) {
            return 'Other';
        }

        return config("audit.table_categories.$table", 'Other');
    }
}
