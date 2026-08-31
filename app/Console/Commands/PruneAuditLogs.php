<?php

namespace App\Console\Commands;

use DB;
use Illuminate\Console\Command;

class PruneAuditLogs extends Command
{
    protected $signature = 'audit:prune';

    protected $description = 'Deletes activity_logs / login_sessions / failed_logins rows older than config(audit.retention_days).';

    public function handle(): int
    {
        $days = config('audit.retention_days');

        if (!$days) {
            $this->info('Retention is set to "forever" (audit.retention_days is null) - nothing to prune.');
            return self::SUCCESS;
        }

        $tz = config('audit.timezone', 'Europe/London');
        $cutoff = now($tz)->subDays($days);

        $deletedLogs = DB::table('activity_logs')->where('created_at', '<', $cutoff)->delete();
        $deletedSessions = DB::table('login_sessions')->where('created_at', '<', $cutoff)->delete();
        $deletedFailed = DB::table('failed_logins')->where('created_at', '<', $cutoff)->delete();

        $this->info("Pruned {$deletedLogs} activity_logs, {$deletedSessions} login_sessions, {$deletedFailed} failed_logins older than {$days} days.");

        // Log the cleanup itself - swallowed on failure so a logging
        // problem never fails the scheduled command.
        try {
            DB::table('activity_logs')->insert([
                'action'      => 'security',
                'severity'    => 'info',
                'category'    => 'Security',
                'module'      => 'Audit Retention',
                'description' => "Pruned audit records older than {$days} days (logs: {$deletedLogs}, sessions: {$deletedSessions}, failed logins: {$deletedFailed}).",
                'created_at'  => now($tz),
                'updated_at'  => now($tz),
            ]);
        } catch (\Throwable $e) {
            // deliberately swallowed - see fail-safe logging note above.
        }

        return self::SUCCESS;
    }
}