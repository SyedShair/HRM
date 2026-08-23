<?php

namespace App\Http\Controllers\Admin;

use DB;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use App\Classes\table;
use App\Classes\permission;
use App\Services\RotaMailer;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Barryvdh\DomPDF\Facade\Pdf;

class SchedulesController extends Controller
{
    private const DAYS = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

    /**
     * GET /schedules - route name('schedule') points at index(); the real
     * logic lives in rota() so both /schedules and /staff-rota render the
     * same employee list, filterable by ?company_id=.
     */
    public function index(Request $request)
    {
        return $this->rota($request);
    }

    /**
     * GET /staff-rota
     * Employee list with current-or-upcoming schedule attached,
     * filterable by ?company_id=.
     */
    public function rota(Request $request)
    {
        if (permission::permitted('schedules-add') == 'fail') { return redirect()->route('denied'); }

        $companies = table::company()->orderBy('company')->get();

        $companyId = $request->query('company_id');
        $companyId = ($companyId !== null && is_numeric($companyId)) ? (int) $companyId : null;

        if (!$companyId && $companies->isNotEmpty()) {
            $companyId = $companies->first()->id;
        }

        $employees = table::people()
            ->join('tbl_company_data', 'tbl_people.id', '=', 'tbl_company_data.reference')
            ->where('tbl_people.employmentstatus', 'Active')
            ->when($companyId, fn ($q) => $q->where('tbl_company_data.company_id', $companyId))
            ->orderBy('tbl_people.lastname')
            ->get([
                'tbl_people.id', 'tbl_people.firstname', 'tbl_people.lastname', 'tbl_people.avatar',
                'tbl_company_data.jobposition', 'tbl_company_data.idno',
            ]);

        $today = date('Y-m-d');

        // FIX: previously this query only ever fetched non-archived
        // (archive = '0') schedules, which meant the instant a schedule
        // was archived it vanished from this list completely - with no
        // way to see it, print it, or permanently delete it again. We
        // now pull every schedule per employee (archived or not) and
        // decide which one to surface per row below: current > upcoming
        // > most recently archived.
        $schedules = table::schedules()
            ->whereIn('reference', $employees->pluck('id'))
            ->orderBy('datefrom', 'desc')
            ->get()
            ->groupBy('reference');

        $employees->transform(function ($emp) use ($schedules, $today) {
            $empSchedules = $schedules->get($emp->id, collect());
            $activeSet = $empSchedules->where('archive', '0');

            $emp->currentSchedule = $activeSet->first(function ($s) use ($today) {
                return $s->datefrom <= $today && $s->dateto >= $today;
            });

            $emp->nextSchedule = $emp->currentSchedule
                ? null
                : $activeSet->filter(fn ($s) => $s->datefrom > $today)->sortBy('datefrom')->first();

            // Only surface an archived schedule when there's nothing
            // live or upcoming to show instead, so an employee who
            // already has an active rota doesn't get cluttered with old
            // archived ones sitting alongside it.
            $emp->archivedSchedule = (!$emp->currentSchedule && !$emp->nextSchedule)
                ? $empSchedules->where('archive', '1')->sortByDesc('datefrom')->first()
                : null;

            return $emp;
        });

        return view('admin.schedules.index', compact('employees', 'companies', 'companyId'));
    }

    /**
     * GET /schedules/new/{employeeId}
     */
    public function create($employeeId)
    {
        if (permission::permitted('schedules-add') == 'fail') { return redirect()->route('denied'); }

        $employee = table::people()->where('id', $employeeId)->first();

        if (!$employee) {
            return redirect('staff-rota')->with('error', trans('Employee not found.'));
        }

        $companyData = table::companydata()->where('reference', $employeeId)->first();
        $days = self::DAYS;

        return view('admin.schedules.form', compact('employee', 'companyData', 'days'));
    }

    /**
     * POST /schedules/add
     */
    public function add(Request $request)
    {
        if (permission::permitted('schedules-add') == 'fail') { return redirect()->route('denied'); }

        $request->validate([
            'reference'    => 'required|integer|exists:tbl_people,id',
            'datefrom'     => 'required|date',
            'dateto'       => 'required|date|after_or_equal:datefrom',
            'weekly_hours' => 'required|numeric|min:0|max:168',
            'is_off'       => 'nullable|array',
            'time_in'      => 'nullable|array',
            'time_in.*'    => 'nullable|date_format:H:i',
            'time_out'     => 'nullable|array',
            'time_out.*'   => 'nullable|date_format:H:i',
        ]);

        $employee = table::people()->where('id', $request->reference)->first();

        if (!$employee) {
            return redirect('staff-rota')->withInput()->with('error', trans('Employee not found.'));
        }

        $companyData = table::companydata()->where('reference', $request->reference)->first();

        $overlap = table::schedules()
            ->where('reference', $request->reference)
            ->where('archive', '0')
            ->where('datefrom', '<=', $request->dateto)
            ->where('dateto', '>=', $request->datefrom)
            ->exists();

        if ($overlap) {
            return back()->withInput()->with('error', trans('This employee already has a schedule that overlaps these dates.'));
        }

        $totalHours = $this->calculateScheduledHours($request);
        $weeklyHoursAllowed = (float) $request->weekly_hours;

        if ($totalHours > $weeklyHoursAllowed + 0.001) {
            return back()->withInput()->with('error', trans(
                'The scheduled hours (:total) exceed the weekly hours allowed (:allowed). Please adjust the daily times so the total does not go over the limit.',
                ['total' => number_format($totalHours, 2), 'allowed' => number_format($weeklyHoursAllowed, 2)]
            ));
        }

        $scheduleId = null;

        try {
            DB::transaction(function () use ($request, $employee, $companyData, $weeklyHoursAllowed, &$scheduleId) {
                $scheduleId = $this->saveSchedule($request, $employee, $companyData, $weeklyHoursAllowed);
            });
        } catch (\Exception $e) {
            \Log::error('Failed to create schedule: '.$e->getMessage());
            return back()->withInput()->with('error', trans('Something went wrong while saving this schedule. Please try again.'));
        }

        // Auto-notify the employee by email once the schedule is safely
        // saved. A failed/skipped send (e.g. no email on file) never
        // undoes the save itself - it's just reflected in the message.
        $mailResult = RotaMailer::send($scheduleId, auth()->id());

        $message = trans('Weekly schedule has been created!');
        $message .= $mailResult['sent']
            ? ' '.trans('An email notification has been sent to the employee.')
            : ' '.trans('Note: no email was sent —').' '.$mailResult['reason'];

        return redirect('staff-rota')->with('success', $message);
    }

    /**
     * GET /schedules/edit/{id}
     */
    public function edit($id)
    {
        if (permission::permitted('schedules-edit') == 'fail') { return redirect()->route('denied'); }

        $schedule = table::schedules()->where('id', $id)->first();

        if (!$schedule) {
            return redirect('staff-rota')->with('error', trans('Schedule not found.'));
        }

        $employee = table::people()->where('id', $schedule->reference)->first();
        $companyData = table::companydata()->where('reference', $schedule->reference)->first();

        $shifts = table::weeklyshifts()
            ->where('schedual_id', $schedule->id)
            ->get()
            ->keyBy('day');

        $days = self::DAYS;

        return view('admin.schedules.form', compact('employee', 'companyData', 'days', 'schedule', 'shifts'));
    }

    /**
     * POST /schedules/update - id comes from a hidden field, not the URL.
     */
    public function update(Request $request)
    {
        if (permission::permitted('schedules-edit') == 'fail') { return redirect()->route('denied'); }

        $request->validate([
            'id'           => 'required|integer|exists:tbl_people_schedules,id',
            'datefrom'     => 'required|date',
            'dateto'       => 'required|date|after_or_equal:datefrom',
            'weekly_hours' => 'required|numeric|min:0|max:168',
            'is_off'       => 'nullable|array',
            'time_in'      => 'nullable|array',
            'time_in.*'    => 'nullable|date_format:H:i',
            'time_out'     => 'nullable|array',
            'time_out.*'   => 'nullable|date_format:H:i',
        ]);

        $schedule = table::schedules()->where('id', $request->id)->first();

        if (!$schedule) {
            return redirect('staff-rota')->with('error', trans('Schedule not found.'));
        }

        $employee = table::people()->where('id', $schedule->reference)->first();
        $companyData = table::companydata()->where('reference', $schedule->reference)->first();

        $overlap = table::schedules()
            ->where('reference', $schedule->reference)
            ->where('archive', '0')
            ->where('id', '!=', $schedule->id)
            ->where('datefrom', '<=', $request->dateto)
            ->where('dateto', '>=', $request->datefrom)
            ->exists();

        if ($overlap) {
            return back()->withInput()->with('error', trans('This employee already has another schedule that overlaps these dates.'));
        }

        $totalHours = $this->calculateScheduledHours($request);
        $weeklyHoursAllowed = (float) $request->weekly_hours;

        if ($totalHours > $weeklyHoursAllowed + 0.001) {
            return back()->withInput()->with('error', trans(
                'The scheduled hours (:total) exceed the weekly hours allowed (:allowed). The rota was not updated - please adjust the daily times.',
                ['total' => number_format($totalHours, 2), 'allowed' => number_format($weeklyHoursAllowed, 2)]
            ));
        }

        $scheduleId = null;

        try {
            DB::transaction(function () use ($request, $employee, $companyData, $schedule, $weeklyHoursAllowed, &$scheduleId) {
                $scheduleId = $this->saveSchedule($request, $employee, $companyData, $weeklyHoursAllowed, $schedule);
            });
        } catch (\Exception $e) {
            \Log::error('Failed to update schedule #'.$request->id.': '.$e->getMessage());
            return back()->withInput()->with('error', trans('Something went wrong while updating this schedule. Please try again.'));
        }

        // Same auto-notify behaviour as add() - the employee should hear
        // about a changed schedule just as much as a brand new one.
        $mailResult = RotaMailer::send($scheduleId, auth()->id());

        $message = trans('Weekly schedule has been updated!');
        $message .= $mailResult['sent']
            ? ' '.trans('An email notification has been sent to the employee.')
            : ' '.trans('Note: no email was sent —').' '.$mailResult['reason'];

        return redirect('staff-rota')->with('success', $message);
    }

    /**
     * GET /schedules/archive/{id}
     */
    public function archive($id)
    {
        if (permission::permitted('schedules-archive') == 'fail') { return redirect()->route('denied'); }

        table::schedules()->where('id', $id)->update(['archive' => '1']);
        table::weeklyshifts()->where('schedual_id', $id)->update(['active' => 0]);

        return redirect('staff-rota')->with('success', trans('Schedule has been archived.'));
    }

    /**
     * GET /schedules/delete/{id}
     * Permanent delete - used both for direct deletes and for the
     * "Delete" action shown on the Rota page against an archived
     * schedule (see admin.schedules.index).
     */
    public function delete($id)
    {
        if (permission::permitted('schedules-delete') == 'fail') { return redirect()->route('denied'); }

        table::weeklyshifts()->where('schedual_id', $id)->delete();
        table::schedules()->where('id', $id)->delete();

        return redirect('staff-rota')->with('success', trans('Schedule has been deleted.'));
    }

    /**
     * GET /schedules/email/{id}
     * Manual "send this rota by email" action, shown as a button on the
     * Rota page next to Edit/Archive. Reuses the exact same mailer as
     * the automatic send-on-save in add()/update() above, just
     * triggered on demand for a schedule that already exists.
     */
    public function emailRota($id)
    {
        if (permission::permitted('schedules-add') == 'fail') { return redirect()->route('denied'); }

        $result = RotaMailer::send((int) $id, auth()->id());

        if ($result['sent']) {
            return redirect('staff-rota')->with('success', trans('Schedule email sent to the employee.'));
        }

        return redirect('staff-rota')->with('error', $result['reason'] ?? trans('The email could not be sent.'));
    }

    /**
     * GET /today-shifts
     * Live "who's on shift today" board used to take attendance. Was
     * previously showing inaccurate results for several stacked reasons
     * - all fixed below:
     *
     *  1. No `archive = '0'` filter on tbl_people_schedules, so an
     *     employee whose schedule had since been archived (they left,
     *     or the schedule was superseded by a new one) still showed up
     *     as scheduled to work today.
     *  2. No join back to tbl_people / employmentstatus filter, so an
     *     employee who left without their schedule ever being archived
     *     would still appear.
     *  3. "Today" was computed with now()'s default server timezone,
     *     which may not be Europe/London - the attendance modal's own
     *     JS explicitly re-adjusts to UK-local time, which is a strong
     *     sign the two were drifting apart near midnight/DST changes.
     *     Anchored below to config('app.timezone') instead (set that to
     *     'Europe/London' in config/app.php if it isn't already).
     *  4. $todayAttendance was fetched but never actually used - the
     *     blade instead ran a fresh "is this employee present" query
     *     PER ROW (an N+1 query problem). Presence is now resolved once
     *     here and attached to each shift as ->isPresent.
     *  5. No permission check at all, unlike every other method on this
     *     controller - added the same gate rota() uses; change the
     *     permission key below if this page has its own dedicated one.
     *  6. No `->distinct()` - guards against duplicate rows for legacy
     *     data saved before the overlap check existed elsewhere in this
     *     controller.
     *  7. The displayed employee name came from s.employee - a text
     *     snapshot written once when the schedule was saved and never
     *     refreshed - while the "Make Attendance" button submitted
     *     s.reference (the actual id). If a name changed after the
     *     schedule was created, those two could disagree: the row
     *     showed one name but attendance was recorded against whoever
     *     s.reference really was. Now both the name and the id are
     *     selected from the same live tbl_people row, so they can't
     *     drift apart.
     */
   /**
 * GET /today-shifts
 * Live "who's on shift today" board used to take attendance.
 * Filterable by ?company_id=, same convention as /staff-rota and
 * the weekly dashboard.
 */
public function todayShifts(Request $request)
{
    if (permission::permitted('schedules-add') == 'fail') { return redirect()->route('denied'); }

    $companies = table::company()->orderBy('company')->get();

    $companyId = $request->query('company_id');
    $companyId = ($companyId !== null && is_numeric($companyId)) ? (int) $companyId : null;

    if (!$companyId && $companies->isNotEmpty()) {
        $companyId = $companies->first()->id;
    }

    // Anchor everything "today" to a single Carbon instance in the
    // app's configured timezone, so the day name, the date, and the
    // "is this shift running right now" check in the view can never
    // disagree with each other.
    $now = Carbon::now(config('app.timezone'));
    $todayDay  = $now->format('l');
    $todayDate = $now->format('Y-m-d');
    $nowTime   = $now->format('H:i'); // matches the H:i format time_in/time_out are stored/validated in

    $shiftsQuery = DB::table('tbl_people_schedules as s')
        ->join('weekly_shifts as w', 's.id', '=', 'w.schedual_id')
        ->join('tbl_people as p', 'p.id', '=', 's.reference')
        ->join('tbl_company_data as cd', 'cd.reference', '=', 'p.id')
        ->where('w.day', $todayDay)
        ->where('w.active', 1)
        // Archived schedules must never surface on today's shift board.
        ->where('s.archive', '0')
        // Only currently-active employees.
        ->where('p.employmentstatus', 'Active')
        ->where(function ($q) use ($todayDate) {
            $q->whereDate('s.datefrom', '<=', $todayDate)
              ->where(function ($q2) use ($todayDate) {
                  $q2->whereDate('s.dateto', '>=', $todayDate)
                     ->orWhereNull('s.dateto');
              });
        })
        // Company scoping - same convention used across the rest of
        // this controller (staff-rota, weekly dashboard, monthly rota).
        ->when($companyId, fn ($q) => $q->where('cd.company_id', $companyId))
        // IMPORTANT: the displayed name is built live from tbl_people
        // (p.firstname/p.lastname) instead of reading s.employee - a
        // text snapshot written once when the schedule was created/
        // last edited and never touched again. Pulling both the name
        // and the id from the same joined p.* row makes it impossible
        // for the displayed name and the id "Make Attendance" submits
        // to disagree.
        ->selectRaw("CONCAT(p.lastname, ', ', p.firstname) as employee")
        ->addSelect('p.id as reference', 'w.day', 'w.time_in', 'w.time_out', 'w.is_off')
        ->distinct()
        ->orderBy('p.lastname')
        ->orderBy('p.firstname');

    $shifts = $shiftsQuery->get();

    $todayAttendance = DB::table('tbl_people_attendance')
        ->where('date', $todayDate)
        ->get();

    $presentReferences = $todayAttendance->pluck('reference')->all();

    $shifts->transform(function ($shift) use ($presentReferences) {
        $shift->isPresent = in_array($shift->reference, $presentReferences);
        return $shift;
    });

    return view('today_shift', compact(
        'shifts',
        'todayDay',
        'todayDate',
        'nowTime',
        'todayAttendance',
        'companies',
        'companyId'
    ));
}

    /**
     * GET /rota/pdf/{id}
     * Printable single-employee weekly schedule, viewable inline (not
     * force-downloaded). $id is a tbl_people_schedules row id, same as
     * edit()/archive()/delete() above - works for active, upcoming, or
     * archived schedules alike.
     */
    public function rotaPdf($id)
    {
        if (permission::permitted('schedules-add') == 'fail') { return redirect()->route('denied'); }

        $schedule = table::schedules()->where('id', $id)->first();

        if (!$schedule) {
            return redirect('staff-rota')->with('error', trans('Schedule not found.'));
        }

        $employee = table::people()->where('id', $schedule->reference)->first();
        $companyData = table::companydata()->where('reference', $schedule->reference)->first();

        $shifts = table::weeklyshifts()
            ->where('schedual_id', $schedule->id)
            ->get()
            ->keyBy('day');

        $days = self::DAYS;

        $pdf = Pdf::loadView('admin.schedules.pdf', compact('employee', 'companyData', 'schedule', 'shifts', 'days'))
            ->setPaper('a4', 'portrait');

        $filename = mb_strtoupper($employee->firstname.'-'.$employee->lastname.'-weekly-schedule').'.pdf';

        return $pdf->stream($filename);
    }

    /**
     * GET /staff-rota/weekly-dashboard
     * On-screen, company-wide "who works when" board for the current
     * week - every active employee with a schedule in effect today,
     * color-coded by shift period, optionally scoped to one company.
     * Shares buildWeeklyRotaData() with weeklyRotaPdf() below so the
     * on-screen and printable versions can never silently drift apart
     * on which bugs are/aren't fixed.
     */
    public function weeklyDashboard(Request $request)
    {
        if (permission::permitted('schedules-add') == 'fail') { return redirect()->route('denied'); }

        $companies = table::company()->orderBy('company')->get();

        $companyId = $request->query('company_id');
        $companyId = ($companyId !== null && is_numeric($companyId)) ? (int) $companyId : null;

        if (!$companyId && $companies->isNotEmpty()) {
            $companyId = $companies->first()->id;
        }

        [$employees, $weeklyShifts] = $this->buildWeeklyRotaData($companyId);

        $days = self::DAYS;

        $weekStart = Carbon::now(config('app.timezone'))->startOfWeek(Carbon::MONDAY);
        $weekEnd = $weekStart->copy()->endOfWeek(Carbon::SUNDAY);

        return view('admin.schedules.weekly-dashboard', compact(
            'employees', 'weeklyShifts', 'days', 'companies', 'companyId', 'weekStart', 'weekEnd'
        ));
    }

    /**
     * GET /staff-rota/weekly-pdf
     * Printable, company-wide weekly staff rota (every employee on one
     * sheet). Renamed from a plain `rotaPdf()` - PHP can't have two
     * methods with the same name in one class, and rotaPdf($id) above
     * already exists for the single-employee PDF. Same bug fixes as
     * weeklyDashboard() above, via the shared buildWeeklyRotaData().
     *
     * FIX: this used to load a bare, top-level view('rota-pdf'), while
     * every other view in this controller lives under the
     * 'admin.schedules.*' namespace (admin.schedules.pdf,
     * admin.schedules.weekly-dashboard, admin.schedules.monthly-
     * dashboard) - that inconsistency is why the view could not be
     * found. Pointed at admin.schedules.weekly-pdf to match, and that
     * file now exists alongside the others.
     */
    public function weeklyRotaPdf(Request $request)
    {
        if (permission::permitted('schedules-add') == 'fail') { return redirect()->route('denied'); }

        $companyId = $request->query('company_id');
        $companyId = ($companyId !== null && is_numeric($companyId)) ? (int) $companyId : null;

        [$employees, $weeklyShifts] = $this->buildWeeklyRotaData($companyId);
        $days = self::DAYS;

        $pdf = Pdf::loadView('admin.schedules.weekly-pdf', compact('employees', 'weeklyShifts', 'days'))
            ->setPaper('a4', 'landscape');

        return $pdf->stream('weekly-rota.pdf');
    }

    /**
     * GET /monthly-rota
     * Calendar-grid version of the same rota: every date in the target
     * month (default: current month), each mapped to that date's day-of-
     * week shift from the employee's schedule pattern - the underlying
     * data is the same recurring weekly_shifts pattern used by the
     * weekly dashboard/PDF above, just repeated across every date in the
     * month instead of the 7 weekday columns.
     *
     * Was previously routed (routes/web.php already reserved
     * /monthly-rota and /monthly-rota/pdf with a comment describing this
     * exact behaviour) but had no matching controller method at all, so
     * both routes threw a "method does not exist" error.
     *
     * ?month=YYYY-MM selects a different month; ?company_id= scopes to
     * one company, same as the weekly views.
     */
    public function monthlyRota(Request $request)
    {
        if (permission::permitted('schedules-add') == 'fail') { return redirect()->route('denied'); }

        $companies = table::company()->orderBy('company')->get();

        $companyId = $request->query('company_id');
        $companyId = ($companyId !== null && is_numeric($companyId)) ? (int) $companyId : null;

        if (!$companyId && $companies->isNotEmpty()) {
            $companyId = $companies->first()->id;
        }

        [$monthStart, $monthEnd, $referenceDate] = $this->resolveMonthRange($request->query('month'));

        [$employees, $weeklyShifts] = $this->buildWeeklyRotaData($companyId, $referenceDate);

        $dates = CarbonPeriod::create($monthStart, $monthEnd);

        return view('admin.schedules.monthly-dashboard', compact(
            'employees', 'weeklyShifts', 'dates', 'monthStart', 'monthEnd', 'companies', 'companyId'
        ));
    }

    /**
     * GET /monthly-rota/pdf
     * Printable equivalent of monthlyRota() above, same data source.
     */
    public function monthlyRotaPdf(Request $request)
    {
        if (permission::permitted('schedules-add') == 'fail') { return redirect()->route('denied'); }

        $companyId = $request->query('company_id');
        $companyId = ($companyId !== null && is_numeric($companyId)) ? (int) $companyId : null;

        [$monthStart, $monthEnd, $referenceDate] = $this->resolveMonthRange($request->query('month'));

        [$employees, $weeklyShifts] = $this->buildWeeklyRotaData($companyId, $referenceDate);

        $dates = CarbonPeriod::create($monthStart, $monthEnd);

        // a3 landscape - a normal a4 sheet can't comfortably fit up to
        // 31 date columns plus the staff column.
        $pdf = Pdf::loadView('monthly-rota-pdf', compact('employees', 'weeklyShifts', 'dates', 'monthStart', 'monthEnd'))
            ->setPaper('a3', 'landscape');

        return $pdf->stream('monthly-rota-'.$monthStart->format('Y-m').'.pdf');
    }

    /**
     * Parses an optional ?month=YYYY-MM query value into
     * [monthStart, monthEnd, referenceDate]. Falls back to the current
     * month on anything missing or malformed, rather than letting a bad
     * query string produce a confusing date-parsing error.
     *
     * referenceDate is the 15th of the target month - used to decide
     * which schedule counts as "current" for that month (see
     * buildWeeklyRotaData()'s $referenceDate param) - picking the 1st or
     * last day risks missing a schedule that starts or ends mid-month.
     *
     * @param string|null $monthParam
     * @return array{0: \Carbon\Carbon, 1: \Carbon\Carbon, 2: \Carbon\Carbon}
     */
    private function resolveMonthRange($monthParam)
    {
        $tz = config('app.timezone');

        $monthStart = ($monthParam && preg_match('/^\d{4}-\d{2}$/', $monthParam))
            ? Carbon::createFromFormat('Y-m-d', $monthParam.'-01', $tz)->startOfDay()
            : Carbon::now($tz)->startOfMonth();

        $monthEnd = $monthStart->copy()->endOfMonth()->startOfDay();
        $referenceDate = $monthStart->copy()->addDays(14);

        return [$monthStart, $monthEnd, $referenceDate];
    }

    /**
     * Shared data source for weeklyDashboard() and weeklyRotaPdf().
     * Originally written inline in a one-off rotaPdf() and had three
     * real bugs, all fixed here:
     *
     *  1. No filter on datefrom/dateto at all - every un-archived
     *     schedule showed up regardless of whether it had even started
     *     yet, or had already ended and simply never got archived. Now
     *     scoped to only the schedule actually in effect today.
     *  2. No employmentstatus filter - an employee who left without
     *     their schedule being archived would still appear, same class
     *     of bug fixed earlier in todayShifts().
     *  3. If an employee somehow had more than one un-archived schedule
     *     at once, weekly_shifts were pulled for ALL of them with no way
     *     to tell which day belonged to which - the view's ->first()
     *     lookup would then arbitrarily pick one, possibly showing a
     *     future/wrong schedule's hours for a given day. Fixed by
     *     scoping weekly_shifts strictly to the one schedule id already
     *     resolved as "current" per employee, not just "any of their
     *     un-archived schedules".
     *
     * @param int|null $companyId
     * @param \Carbon\Carbon|string|null $referenceDate Date used to decide which
     *        schedule is "current" - defaults to today. monthlyRota()
     *        below passes a day inside whichever month is being viewed,
     *        so a past/future month resolves against a schedule that
     *        was/will be active *then*, not necessarily today.
     * @return array{0: \Illuminate\Support\Collection, 1: \Illuminate\Support\Collection} [$employees, $weeklyShifts]
     */
    private function buildWeeklyRotaData($companyId = null, $referenceDate = null)
    {
        $today = $referenceDate
            ? Carbon::parse($referenceDate)->format('Y-m-d')
            : Carbon::now(config('app.timezone'))->format('Y-m-d');

        $employeesQuery = DB::table('tbl_people_schedules as s')
            ->join('tbl_people as p', 'p.id', '=', 's.reference')
            ->where('s.archive', '0')
            ->where('p.employmentstatus', 'Active')
            // Only the schedule actually in effect today - see fix
            // note #1 above.
            ->where('s.datefrom', '<=', $today)
            ->where(function ($q) use ($today) {
                $q->where('s.dateto', '>=', $today)->orWhereNull('s.dateto');
            });

        if ($companyId) {
            $employeesQuery
                ->join('tbl_company_data as cd', 'cd.reference', '=', 'p.id')
                ->where('cd.company_id', $companyId);
        }

        $employees = $employeesQuery
            ->select('s.id as schedule_id', 's.reference', 's.idno', 'p.firstname', 'p.lastname')
            ->distinct()
            ->orderBy('p.lastname')
            ->orderBy('p.firstname')
            ->get();

        if ($employees->isEmpty()) {
            return [$employees, collect()];
        }

        // Scoped to exactly the schedule ids resolved above (not "any
        // un-archived schedule for this reference") - see fix note #3
        // above.
        $weeklyShifts = DB::table('weekly_shifts as ws')
            ->whereIn('ws.schedual_id', $employees->pluck('schedule_id'))
            ->where('ws.active', 1)
            ->select('ws.schedual_id', 'ws.day', 'ws.time_in', 'ws.time_out', 'ws.is_off')
            ->get();

        // Attach 'reference' onto each shift row (keyed off the same
        // schedule_id -> reference map already resolved above) so the
        // view's existing ->where('reference', $employee->reference)
        // lookup keeps working exactly as written.
        $scheduleToReference = $employees->pluck('reference', 'schedule_id');
        $weeklyShifts = $weeklyShifts->map(function ($shift) use ($scheduleToReference) {
            $shift->reference = $scheduleToReference[$shift->schedual_id] ?? null;
            return $shift;
        });

        return [$employees, $weeklyShifts];
    }

    /**
     * Sums scheduled hours across all non-off days from the submitted
     * form. Shared by add() and update() so the cap is enforced identically.
     */
    private function calculateScheduledHours(Request $request): float
    {
        $isOff = $request->input('is_off', []);
        $timeIn = $request->input('time_in', []);
        $timeOut = $request->input('time_out', []);

        $totalMinutes = 0;

        foreach (self::DAYS as $day) {
            if (in_array($day, $isOff)) {
                continue;
            }

            $in = $timeIn[$day] ?? null;
            $out = $timeOut[$day] ?? null;

            if ($in && $out) {
                $minutes = (strtotime($out) - strtotime($in)) / 60;
                if ($minutes < 0) {
                    $minutes += 24 * 60; // overnight shift
                }
                $totalMinutes += $minutes;
            }
        }

        return round($totalMinutes / 60, 2);
    }

    /**
     * Writes the tbl_people_schedules summary row and its 7
     * weekly_shifts detail rows. hours = the weekly hours ALLOWED
     * figure the user entered (the contracted target), not a computed total.
     *
     * @return int the tbl_people_schedules.id that was written to (new
     *             or existing), so callers can email the employee about it.
     */
    private function saveSchedule(Request $request, $employee, $companyData, float $weeklyHoursAllowed, $existingSchedule = null): int
    {
        $isOff = $request->input('is_off', []);
        $timeIn = $request->input('time_in', []);
        $timeOut = $request->input('time_out', []);

        $restDays = [];
        $firstWorkingTimeIn = null;
        $firstWorkingTimeOut = null;

        foreach (self::DAYS as $day) {
            if (in_array($day, $isOff)) {
                $restDays[] = $day;
                continue;
            }

            $in = $timeIn[$day] ?? null;
            $out = $timeOut[$day] ?? null;

            if ($in && $out && $firstWorkingTimeIn === null) {
                $firstWorkingTimeIn = $in;
                $firstWorkingTimeOut = $out;
            }
        }

        $employeeName = mb_strtoupper($employee->lastname.', '.$employee->firstname);
        $idno = $companyData->idno ?? null;

        $scheduleAttributes = [
            'reference' => $employee->id,
            'idno'      => $idno,
            'employee'  => $employeeName,
            'intime'    => $firstWorkingTimeIn,
            'outime'    => $firstWorkingTimeOut,
            'datefrom'  => $request->datefrom,
            'dateto'    => $request->dateto,
            'hours'     => (string) $weeklyHoursAllowed,
            'restday'   => implode(',', $restDays),
            'archive'   => '0',
        ];

        if ($existingSchedule) {
            table::schedules()->where('id', $existingSchedule->id)->update($scheduleAttributes);
            $scheduleId = $existingSchedule->id;
            table::weeklyshifts()->where('schedual_id', $scheduleId)->delete();
        } else {
            $scheduleId = table::schedules()->insertGetId($scheduleAttributes);
        }

        $shiftRows = [];
        foreach (self::DAYS as $day) {
            $dayOff = in_array($day, $isOff);
            $in = !$dayOff ? ($timeIn[$day] ?? null) : null;
            $out = !$dayOff ? ($timeOut[$day] ?? null) : null;

            $shiftRows[] = [
                'schedual_id' => $scheduleId,
                'day'         => $day,
                'time_in'     => $in ?: null,
                'time_out'    => $out ?: null,
                'is_off'      => $dayOff ? 1 : 0,
                'active'      => 1,
                'created_at'  => now(),
                'updated_at'  => now(),
            ];
        }

        table::weeklyshifts()->insert($shiftRows);

        return $scheduleId;
    }
}