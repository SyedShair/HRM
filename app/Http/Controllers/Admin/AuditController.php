<?php

namespace App\Http\Controllers\Admin;

use DB;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\AuditService;

class AuditController extends Controller
{
    public function index(Request $request)
    {
        $query = DB::table('activity_logs');

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('user_name', 'like', "%$s%")
                  ->orWhere('ip_address', 'like', "%$s%")
                  ->orWhere('action', 'like', "%$s%")
                  ->orWhere('module', 'like', "%$s%")
                  ->orWhere('table_name', 'like', "%$s%")
                  ->orWhere('record_id', 'like', "%$s%")
                  ->orWhere('url', 'like', "%$s%")
                  ->orWhere('user_id', 'like', "%$s%");
            });
        }

        foreach (['role', 'category', 'action', 'module', 'severity', 'user_id'] as $field) {
            if ($request->filled($field)) {
                $query->where($field, $request->input($field));
            }
        }

        $this->applyDateRange($query, $request);

        $activities = $query->orderByDesc('created_at')->paginate(25)->withQueryString();

        $stats = $this->buildStats();

        $filters = [
            'roles'      => DB::table('activity_logs')->distinct()->whereNotNull('role')->orderBy('role')->pluck('role'),
            'categories' => DB::table('activity_logs')->distinct()->orderBy('category')->pluck('category'),
            'actions'    => DB::table('activity_logs')->distinct()->orderBy('action')->pluck('action'),
            'modules'    => DB::table('activity_logs')->distinct()->whereNotNull('module')->orderBy('module')->pluck('module'),
            'severities' => ['info', 'success', 'warning', 'danger'],
        ];

        return view('admin.audit.index', compact('activities', 'stats', 'filters'));
    }

    public function show($id)
    {
        $activity = DB::table('activity_logs')->where('id', $id)->first();

        if (!$activity) {
            return redirect()->route('audit.index')->with('error', 'Activity not found.');
        }

        $oldData = $activity->old_data ? json_decode($activity->old_data, true) : null;
        $newData = $activity->new_data ? json_decode($activity->new_data, true) : null;
        $metadata = $activity->metadata ? json_decode($activity->metadata, true) : null;

        return view('admin.audit.show', compact('activity', 'oldData', 'newData', 'metadata'));
    }

    public function sessions()
    {
        $timeout = config('audit.session_timeout_minutes', 15);
        $cutoff = now()->subMinutes($timeout);

        // Lazily flip stale "online" rows to "expired" whenever this
        // page is viewed - no scheduled job required for this to stay
        // reasonably accurate.
        DB::table('login_sessions')
            ->whereNull('logout_at')
            ->where('last_activity_at', '<', $cutoff)
            ->update(['status' => 'expired', 'updated_at' => now()]);

        $sessions = DB::table('login_sessions')
            ->orderByDesc('last_activity_at')
            ->paginate(25);

        return view('admin.audit.sessions', compact('sessions', 'timeout'));
    }

    /**
     * CSV export is fully wired up and dependency-free. Excel/PDF are
     * intentionally left as a clear extension point (see the abort()
     * below) rather than silently pulling in maatwebsite/excel or
     * similar - wire your app's existing PDF package (Barryvdh\DomPDF,
     * already used elsewhere in this app) into that branch using the
     * same $rows collection.
     */
    public function export($format, Request $request)
    {
        $query = DB::table('activity_logs');

        if ($request->filled('search')) {
            $query->where('user_name', 'like', '%'.$request->search.'%');
        }

        $this->applyDateRange($query, $request);

        $rows = $query->orderByDesc('created_at')->limit(5000)->get();

        AuditService::log([
            'action'      => 'export',
            'severity'    => 'info',
            'category'    => 'Reports',
            'module'      => 'Audit Log',
            'description' => "Exported audit log as {$format}",
        ]);

        if ($format === 'csv') {
            return $this->streamCsv($rows);
        }

        abort(501, 'Only CSV export is wired up out of the box - see AuditController::export() docblock for how to add Excel/PDF.');
    }

    protected function streamCsv($rows)
    {
        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="audit-log-'.now()->format('Ymd_His').'.csv"',
        ];

        return response()->stream(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['ID', 'User', 'Role', 'Category', 'Action', 'Module', 'Table', 'Record ID', 'Severity', 'IP', 'Date']);

            foreach ($rows as $r) {
                fputcsv($out, [
                    $r->id, $r->user_name, $r->role, $r->category, $r->action,
                    $r->module, $r->table_name, $r->record_id, $r->severity,
                    $r->ip_address, $r->created_at,
                ]);
            }

            fclose($out);
        }, 200, $headers);
    }

    protected function applyDateRange($query, Request $request): void
    {
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->filled('range')) {
            match ($request->range) {
                'today'     => $query->whereDate('created_at', today()),
                'yesterday' => $query->whereDate('created_at', today()->subDay()),
                'week'      => $query->where('created_at', '>=', now()->startOfWeek()),
                'month'     => $query->where('created_at', '>=', now()->startOfMonth()),
                default     => null,
            };
        }
    }

    protected function buildStats(): array
    {
        $today = today();

        return [
            'total_today'   => DB::table('activity_logs')->whereDate('created_at', $today)->count(),
            'logins_today'  => DB::table('activity_logs')->whereDate('created_at', $today)->where('action', 'login')->count(),
            'logouts_today' => DB::table('activity_logs')->whereDate('created_at', $today)->where('action', 'logout')->count(),
            'failed_logins' => DB::table('failed_logins')->whereDate('attempted_at', $today)->count(),
            'creates_today' => DB::table('activity_logs')->whereDate('created_at', $today)->where('action', 'create')->count(),
            'updates_today' => DB::table('activity_logs')->whereDate('created_at', $today)->where('action', 'update')->count(),
            'deletes_today' => DB::table('activity_logs')->whereDate('created_at', $today)->where('action', 'delete')->count(),
            'online_now'    => DB::table('login_sessions')->where('status', 'online')->whereNull('logout_at')->count(),
        ];
    }
}
