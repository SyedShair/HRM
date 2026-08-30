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

        // Switched from ->paginate() to a capped ->get(): the view uses
        // DataTables.js for client-side paging/searching (matching every
        // other list page in this app), which needs the full filtered
        // result set in one response rather than one page at a time.
        // The row cap (config('audit.dashboard_row_limit')) is a safety
        // net against sending an unbounded result set to the browser -
        // narrow the filters above to see further back than it covers.
        $activities = $query->orderByDesc('created_at')
            ->limit(config('audit.dashboard_row_limit', 1000))
            ->get();

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

    /**
     * Permanently deletes the selected activity_logs rows. Only reachable
     * behind the same admin gate as the rest of this controller. The
     * deletion itself is logged as a Security/danger event (with the
     * deleted ids kept in metadata) so removing audit records doesn't
     * itself go unaudited.
     */
    public function bulkDelete(Request $request)
    {
        $ids = array_values(array_filter((array) $request->input('ids', []), 'is_numeric'));

        if (empty($ids)) {
            return back()->with('error', trans('No records were selected.'));
        }

        $count = DB::table('activity_logs')->whereIn('id', $ids)->delete();

        AuditService::log([
            'action'      => 'security',
            'severity'    => 'danger',
            'category'    => 'Security',
            'module'      => 'Audit Log',
            'description' => "Manually deleted {$count} activity log record(s).",
            'metadata'    => ['deleted_ids' => $ids],
        ]);

        return back()->with('success', trans(':count record(s) deleted.', ['count' => $count]));
    }

    /**
     * Same as bulkDelete() above, but for login_sessions rows on the
     * Live Sessions page.
     */
    public function bulkDeleteSessions(Request $request)
    {
        $ids = array_values(array_filter((array) $request->input('ids', []), 'is_numeric'));

        if (empty($ids)) {
            return back()->with('error', trans('No sessions were selected.'));
        }

        $count = DB::table('login_sessions')->whereIn('id', $ids)->delete();

        AuditService::log([
            'action'      => 'security',
            'severity'    => 'danger',
            'category'    => 'Security',
            'module'      => 'Audit Log',
            'description' => "Manually deleted {$count} session record(s).",
            'metadata'    => ['deleted_ids' => $ids],
        ]);

        return back()->with('success', trans(':count session(s) deleted.', ['count' => $count]));
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

        // TEMPORARY: the join to users_roles assumed a `role` column
        // that doesn't actually exist (confirmed by the 1054 error this
        // caused), so it's removed for now - showing acc_type only
        // until the real column name is confirmed. Once known, add
        // back: ->leftJoin('users_roles as ur', 'ur.id', '=', 'u.role_id')
        // and 'ur.<real_column> as role_name' in the select below.
        $sessions = DB::table('login_sessions as ls')
            ->leftJoin('users as u', 'u.id', '=', 'ls.user_id')
            ->select(
                'ls.*',
                'u.name as user_name',
                'u.acc_type as acc_type'
            )
            ->orderByDesc('ls.last_activity_at')
            ->limit(config('audit.dashboard_row_limit', 1000))
            ->get();

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