<?php

return [

    // Master on/off switch. Set AUDIT_ENABLED=false in .env to disable
    // everything (query hooks, page tracking, auth listeners) without
    // touching code - useful if the audit system ever needs to be
    // switched off in a hurry.
    'enabled' => env('AUDIT_ENABLED', true),

    // Tables that INSERT/UPDATE/DELETE tracking will never log - the
    // audit tables themselves (to prevent an infinite loop), plus
    // framework/system tables that produce noise with no business value.
    'excluded_tables' => [
        'activity_logs', 'login_sessions', 'failed_logins',
        'sessions', 'cache', 'cache_locks', 'jobs', 'failed_jobs',
        'job_batches', 'migrations', 'password_reset_tokens',
        'personal_access_tokens',
    ],

    // Maps a database table name to the human-readable category shown
    // on the dashboard. Anything not listed here falls back to 'Other' -
    // add a line here whenever you add a new table you want categorised
    // rather than lumped into "Other".
    'table_categories' => [
        'tbl_people'            => 'Employees',
        'tbl_company_data'      => 'Employees',
        'tbl_address_history'   => 'Employees',
        'tbl_people_attendance' => 'Attendance',
        'tbl_people_schedules'  => 'Attendance',
        'weekly_shifts'         => 'Attendance',
        'tbl_people_leaves'     => 'Leave',
        'users'                 => 'Users',
        'users_roles'           => 'Security',
        'users_permissions'     => 'Security',
        'tbl_form_company'      => 'Settings',
        'tbl_form_department'   => 'Settings',
        'tbl_form_jobtitle'     => 'Settings',
        'tbl_form_leavetype'    => 'Settings',
        'tbl_form_leavegroup'   => 'Settings',
        'tbl_company_documents' => 'Settings',
        'settings'              => 'Settings',
        'payroll'               => 'Payroll',
    ],

    // Field names (case-insensitive) masked wherever old/new data is
    // captured, e.g. shown as "********" instead of the real value.
    'sensitive_fields' => [
        'password', 'password_confirmation', 'token', 'api_token',
        'remember_token', 'otp', 'otp_code', 'secret',
        'access_token', 'refresh_token',
    ],

    // Days to keep activity_logs / login_sessions / failed_logins rows
    // before `php artisan audit:prune` deletes them. Set to null (or
    // leave AUDIT_RETENTION_DAYS unset with no default) to keep forever -
    // the prune command then does nothing rather than guessing.
    'retention_days' => env('AUDIT_RETENTION_DAYS', 365),

    // Minutes of inactivity before a login_sessions row still lacking a
    // logout_at is treated as 'expired' rather than 'online' on the live
    // sessions view.
    'session_timeout_minutes' => env('AUDIT_SESSION_TIMEOUT', 15),

    // fnmatch()-style patterns (matched against the request path) that
    // are never logged as page-view activity - static assets and the
    // audit dashboard's own pages, so browsing the dashboard doesn't
    // spam the dashboard with "viewed the audit log" entries about
    // itself.
    'excluded_request_patterns' => [
        '*.css', '*.js', '*.map', '*.ico', '*.png', '*.jpg', '*.jpeg',
        '*.gif', '*.svg', '*.woff', '*.woff2', '*.ttf', '*.eot',
        'storage/*', 'assets/*', 'audit', 'audit/*',
    ],

];
