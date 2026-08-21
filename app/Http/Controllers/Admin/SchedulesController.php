<?php

namespace App\Http\Controllers\Admin;
use DB;
use PDF;
use App\Classes\table;
use App\Classes\permission;
use App\Http\Requests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use App\Http\Controllers\Controller;
use Carbon\Carbon;

class SchedulesController extends Controller
{
    /**
     * Canonical day order used everywhere a weekly rota is built or
     * displayed - Monday first, matching the Weekly Rota / PDF views.
     */
    private const WEEK_DAYS = [
        'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday',
    ];

    public function index() 
    {
        if (permission::permitted('schedules')=='fail'){ return redirect()->route('denied'); }
        
        $employee = table::people()->get();
        $schedules = table::schedules()->get();
        $tf = table::settings()->value("time_format");
    
        return view('admin.schedules', compact('employee', 'schedules', 'tf'));
    }

    /**
     * Add a new schedule for an employee: the company's open/close time
     * for that employee, the date range it applies over, and their
     * weekly rest days. Saving this now ALSO auto-generates the full
     * 7-day weekly rota (weekly_shifts) in one step - every non-rest
     * day gets the same open/close time, every rest day is marked off.
     * The separate "Weekly Rota Setup" modal remains available
     * afterward for one-off exceptions to specific days.
     */
    public function add(Request $request) 
    {
        if (permission::permitted('schedules-add')=='fail'){ return redirect()->route('denied'); }

        $v = $request->validate([
            'id' => 'required|max:20',
            'employee' => 'required|max:100',
            'intime' => 'required|max:20',
            'outime' => 'required|max:20',
            'datefrom' => 'required|date|max:15',
            'dateto' => 'required|date|max:15',
            'hours' => 'required|max:6',
            'restday' => 'nullable|array',
            'restday.*' => 'in:' . implode(',', self::WEEK_DAYS),
        ]);

        $id = $request->id;
        $employee = mb_strtoupper($request->employee);
        $intime = date("h:i A", strtotime($request->intime));
        $outime = date("h:i A", strtotime($request->outime));
        $datefrom = $request->datefrom;
        $dateto = $request->dateto;
        $hours = $request->hours;
        $restDays = $request->restday ?? [];
        $restday = !empty($restDays) ? implode(', ', $restDays) : null;

        $ref = table::schedules()->where([['reference', $id],['archive', 0]])->exists();

        if ($ref == 1) 
        {
            return redirect('schedules')->with('error', trans("Oops! This employee has schedule already. Please arhive the present schedule to add new schedule."));
        }

        $emp_id = table::companydata()->where('reference', $id)->value('idno');

        $scheduleId = table::schedules()->insertGetId([
            'reference' => $id,
            'idno' => $emp_id,
            'employee' => $employee,
            'intime' => $intime,
            'outime' => $outime,
            'datefrom' => $datefrom, 
            'dateto' => $dateto,
            'hours' => $hours,
            'restday' => $restday,
            'archive' => '0',
        ]);

        // Build the 7-day weekly rota straight from the open/close time
        // and rest days just submitted - this is what previously had to
        // be typed in by hand, day by day, via the separate modal.
        $this->generateWeeklyShiftsFromSchedule($scheduleId, $request->intime, $request->outime, $restDays);

        return redirect('schedules')->with('success', trans("New Schedule Added!"));
    }

    public function edit($id, Request $request) 
    {
        if (permission::permitted('schedules-edit')=='fail'){ return redirect()->route('denied'); }

        $s = table::schedules()->where('id', $id)->first();
        $r = !empty($s->restday) ? explode(', ', $s->restday) : [];
        $e_id = ($s->id == null) ? 0 : Crypt::encryptString($s->id) ;
        $tf = table::settings()->value("time_format");
        
        return view('admin.edits.edit-schedule', compact('s','r', 'e_id', 'tf'));
    }

    /**
     * Update an existing schedule. Same as add(): saving here
     * regenerates the full weekly rota from the (possibly changed)
     * open/close time and rest days, so the rota always stays in sync
     * with whatever the schedule says. Any one-off day exceptions set
     * via the Weekly Rota Setup modal will be reset back to the
     * schedule's standard hours when this runs - by design, since this
     * is meant to be the single source of truth for the standard week.
     */
    public function update(Request $request) 
    {
        if (permission::permitted('schedules-edit')=='fail'){ return redirect()->route('denied'); }

        $v = $request->validate([
            'id' => 'required|max:200',
            'intime' => 'required|max:20',
            'outime' => 'required|max:20',
            'datefrom' => 'required|date|max:15',
            'dateto' => 'required|date|max:15',
            'hours' => 'required|max:6',
            'restday' => 'nullable|array',
            'restday.*' => 'in:' . implode(',', self::WEEK_DAYS),
        ]);

        $id = Crypt::decryptString($request->id);
        $intime = date("h:i A", strtotime($request->intime));
        $outime = date("h:i A", strtotime($request->outime));
        $datefrom = $request->datefrom; 
        $dateto = $request->dateto; 
        $hours = $request->hours;
        $restDays = $request->restday ?? [];
        $restday = !empty($restDays) ? implode(', ', $restDays) : null;

        table::schedules()
        ->where('id', $id)
        ->update([
                'intime' => $intime,
                'outime' => $outime,
                'datefrom' => $datefrom,
                'dateto' => $dateto,
                'hours' => $hours,
                'restday' => $restday,
        ]);

        $this->generateWeeklyShiftsFromSchedule($id, $request->intime, $request->outime, $restDays);

        return redirect('schedules')->with('success', trans("Schedule has been updated!"));
    }

    public function delete($id, Request $request) 
    {
        if (permission::permitted('schedules-delete')=='fail'){ return redirect()->route('denied'); }

        table::schedules()->where('id', $id)->delete();

        // Weekly shifts are only meaningful attached to a schedule -
        // clean them up too, so deleted schedules don't leave orphaned
        // rota rows behind that could resurface if the id is reused.
        DB::table('weekly_shifts')->where('schedual_id', $id)->delete();

        return redirect('schedules')->with('success', trans("Deleted!"));
    }

    public function archive($id, Request $request)
    {
		if (permission::permitted('schedules-archive')=='fail'){ return redirect()->route('denied'); }
        
		$id = $request->id;
		table::schedules()->where('id', $id)->update(['archive' => '1']);

    	return redirect('schedules')->with('success', trans("Schedule has been archived!"));
   	}

    /**
     * GET WEEKLY DATA (for the "Weekly Rota Setup" override modal).
     */
    public function getWeekly($id)
    {
        $schedule = DB::table('tbl_people_schedules')
            ->where('id', $id)
            ->first();

        if (!$schedule) {
            return response()->json([
                'restDays' => [],
                'shifts' => []
            ]);
        }

        $restDays = [];
        if (!empty($schedule->restday)) {
            $restDays = array_map('trim', explode(',', $schedule->restday));
        }

        $shifts = DB::table('weekly_shifts')
            ->where('schedual_id', $id)
            ->where('active', 1)
            ->get()
            ->keyBy('day');

        return response()->json([
            'restDays' => $restDays,
            'shifts'   => $shifts
        ]);
    }

    /**
     * Manual per-day override, used by the "Weekly Rota Setup" modal
     * for one-off exceptions on top of the schedule's standard hours
     * (e.g. one day this week they finish early). This intentionally
     * only touches the days actually submitted - it does not regenerate
     * the whole week from scratch like generateWeeklyShiftsFromSchedule()
     * does, so it's safe to use for a single-day tweak.
     */
    public function storeWeekly(Request $request)
    {
        $scheduleId = $request->schedule_id;

        $schedule = DB::table('tbl_people_schedules')
            ->where('id', $scheduleId)
            ->first();

        if (!$schedule) {
            return back()->with('error', 'Schedule not found');
        }

        $restDays = [];
        if (!empty($schedule->restday)) {
            $restDays = array_map('trim', explode(',', $schedule->restday));
        }

        $shifts = $request->shift ?? [];
        if (empty($shifts)) {
            return back()->with('error', 'No shift data provided');
        }

        foreach ($shifts as $day => $time) {

            $timeIn  = $time['in'] ?? null;
            $timeOut = $time['out'] ?? null;

            $isOff = in_array($day, $restDays) || !$timeIn || !$timeOut;

            DB::table('weekly_shifts')->updateOrInsert(
                [
                    'schedual_id' => $scheduleId,
                    'day'         => $day,
                ],
                [
                    'time_in'  => $isOff ? null : $this->toTimeOnly($timeIn),
                    'time_out' => $isOff ? null : $this->toTimeOnly($timeOut),
                    'is_off'   => $isOff ? 1 : 0,
                    'active'   => 1,
                ]
            );
        }

        return back()->with('success', 'Weekly rota saved successfully');
    }

    public function pdf($id)
    {
        $schedule = DB::table('tbl_people_schedules')
            ->where('id', $id)
            ->first();

        if (!$schedule) {
            return back()->with('error', 'Schedule not found');
        }

        $shifts = DB::table('weekly_shifts')
            ->where('schedual_id', $id)
            ->where('active', 1)
            ->orderByRaw("FIELD(day,'Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday')")
            ->get();

        $data = [
            'schedule' => $schedule,
            'shifts' => $shifts
        ];

        $pdf = PDF::loadView('weekly-shift', $data)
            ->setOptions(['isRemoteEnabled' => true]);

        return $pdf->stream('weekly-shift-'.$schedule->employee.'.pdf');
    }

    public function todayShifts()
    {
        $todayDay  = now()->format('l');
        $todayDate = now()->format('Y-m-d');

        $employees = DB::table('tbl_people')->get();

        $shifts = DB::table('tbl_people_schedules as s')
            ->join('weekly_shifts as w', 's.id', '=', 'w.schedual_id')
            ->where('w.day', $todayDay)
            ->where('w.active', 1)
            ->where('w.is_off', 0)
            ->where(function($q) use ($todayDate) {
                $q->whereDate('s.datefrom', '<=', $todayDate)
                  ->where(function($q2) use ($todayDate) {
                      $q2->whereDate('s.dateto', '>=', $todayDate)
                         ->orWhereNull('s.dateto');
                  });
            })
            ->select('s.employee','w.day','w.time_in','w.time_out','s.reference')
            ->get();

        $todayAttendance = DB::table('tbl_people_attendance')
            ->where('date', $todayDate)->get();

        return view('today_shift', compact(
            'shifts',
            'todayDay',
            'todayDate',
            'employees',
            'todayAttendance'
        ));
    }

    public function rotaPdf()
    {
        $employees = DB::table('tbl_people_schedules as s')
            ->leftJoin('tbl_people as p', 'p.id', '=', 's.reference')
            ->select(
                's.reference',
                's.idno',
                'p.firstname',
                'p.lastname'
            )
            ->where('s.archive', 0)
            ->groupBy(
                's.reference',
                's.idno',
                'p.firstname',
                'p.lastname'
            )
            ->get();

        $weeklyShifts = DB::table('weekly_shifts as ws')
            ->leftJoin('tbl_people_schedules as s', 's.id', '=', 'ws.schedual_id')
            ->select(
                'ws.day',
                'ws.time_in',
                'ws.time_out',
                'ws.is_off',
                's.reference'
            )
            ->where('ws.active', 1)
            ->get();

        $pdf = Pdf::loadView('rota-pdf', [
            'employees' => $employees,
            'weeklyShifts' => $weeklyShifts,
            'days' => self::WEEK_DAYS,
        ]);

        $pdf->setPaper('a4', 'landscape');

        return $pdf->stream('weekly-rota.pdf');
    }

    public function rota()
    {
        $employees = DB::table('tbl_people_schedules as s')
            ->leftJoin('tbl_people as p', 'p.id', '=', 's.reference')
            ->select(
                's.reference',
                's.idno',
                'p.firstname',
                'p.lastname'
            )
            ->where('s.archive', 0)
            ->groupBy(
                's.reference',
                's.idno',
                'p.firstname',
                'p.lastname'
            )
            ->get();

        $weeklyShifts = DB::table('weekly_shifts as ws')
            ->leftJoin('tbl_people_schedules as s', 's.id', '=', 'ws.schedual_id')
            ->select(
                'ws.day',
                'ws.time_in',
                'ws.time_out',
                'ws.is_off',
                's.reference'
            )
            ->where('ws.active', 1)
            ->get();

        return view(
            'rota',
            compact('employees', 'weeklyShifts')
        );
    }

    /**
     * Monthly rota: a calendar-grid view for one month, built from the
     * same recurring weekly_shifts pattern as the weekly rota - each day
     * of the month is resolved to its weekday's standard shift, but only
     * if that date actually falls inside the employee's schedule's
     * datefrom/dateto range (so someone whose schedule starts mid-month
     * shows blank before that, not a shift they don't actually have yet).
     *
     * @param Request $request  Optional ?month=YYYY-MM, defaults to current month
     */
    public function monthlyRota(Request $request)
    {
        if (permission::permitted('schedules')=='fail'){ return redirect()->route('denied'); }

        [$monthStart, $monthEnd, $month] = $this->resolveMonth($request->query('month'));

        $days = $this->buildDayRange($monthStart, $monthEnd);
        $employees = $this->monthlyRotaEmployees();
        $weeklyShifts = $this->monthlyRotaWeeklyShifts();

        return view('monthly-rota', compact('employees', 'weeklyShifts', 'days', 'month', 'monthStart'));
    }

    /**
     * PDF export of the same monthly grid, landscape for width.
     */
    public function monthlyRotaPdf(Request $request)
    {
        if (permission::permitted('schedules')=='fail'){ return redirect()->route('denied'); }

        [$monthStart, $monthEnd, $month] = $this->resolveMonth($request->query('month'));

        $days = $this->buildDayRange($monthStart, $monthEnd);
        $employees = $this->monthlyRotaEmployees();
        $weeklyShifts = $this->monthlyRotaWeeklyShifts();

        $pdf = Pdf::loadView('monthly-rota-pdf', compact('employees', 'weeklyShifts', 'days', 'month', 'monthStart'));
        $pdf->setPaper('a4', 'landscape');

        return $pdf->stream('monthly-rota-' . $month . '.pdf');
    }

    /**
     * Parses a "YYYY-MM" query param into month boundaries, falling back
     * to the current month for anything missing or malformed.
     *
     * @return array [Carbon $monthStart, Carbon $monthEnd, string $month]
     */
    private function resolveMonth(?string $month): array
    {
        try {
            $monthStart = $month
                ? Carbon::createFromFormat('Y-m', $month)->startOfMonth()
                : now()->startOfMonth();
        } catch (\Exception $e) {
            $monthStart = now()->startOfMonth();
        }

        return [$monthStart, $monthStart->copy()->endOfMonth(), $monthStart->format('Y-m')];
    }

    /**
     * @return \Illuminate\Support\Collection<Carbon>
     */
    private function buildDayRange(Carbon $start, Carbon $end)
    {
        $days = collect();
        for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
            $days->push($date->copy());
        }
        return $days;
    }

    /**
     * Active (non-archived) employees with a schedule, including the
     * schedule's date range so the view can blank out days outside it.
     */
    private function monthlyRotaEmployees()
    {
        return DB::table('tbl_people_schedules as s')
            ->leftJoin('tbl_people as p', 'p.id', '=', 's.reference')
            ->select(
                's.reference',
                's.idno',
                'p.firstname',
                'p.lastname',
                's.datefrom',
                's.dateto'
            )
            ->where('s.archive', 0)
            ->groupBy('s.reference', 's.idno', 'p.firstname', 'p.lastname', 's.datefrom', 's.dateto')
            ->get();
    }

    /**
     * The recurring weekday pattern (Monday..Sunday -> time_in/time_out/is_off)
     * for every active, non-archived schedule - same source data the
     * weekly rota uses, reused here to project across the whole month.
     */
    private function monthlyRotaWeeklyShifts()
    {
        return DB::table('weekly_shifts as ws')
            ->leftJoin('tbl_people_schedules as s', 's.id', '=', 'ws.schedual_id')
            ->select(
                'ws.day',
                'ws.time_in',
                'ws.time_out',
                'ws.is_off',
                's.reference'
            )
            ->where('ws.active', 1)
            ->where('s.archive', 0)
            ->get();
    }

    /**
     * Build/refresh the full 7-day weekly rota for one schedule: every
     * day that isn't a rest day gets the schedule's standard open/close
     * time, every rest day is marked is_off=1 with null times. Existing
     * rows for this schedule are updated in place; missing ones are
     * created - so this is safe to call every time a schedule is saved.
     *
     * @param int $scheduleId
     * @param string $rawTimeIn   Raw submitted open time (any strtotime()-parseable format)
     * @param string $rawTimeOut  Raw submitted close time
     * @param array $restDays     Day names (e.g. ['Sunday','Saturday']) treated as off
     */
    private function generateWeeklyShiftsFromSchedule($scheduleId, $rawTimeIn, $rawTimeOut, array $restDays = [])
    {
        $restDays = array_map('trim', $restDays);

        $timeIn = $this->toTimeOnly($rawTimeIn);
        $timeOut = $this->toTimeOnly($rawTimeOut);

        foreach (self::WEEK_DAYS as $day) {

            $isOff = in_array($day, $restDays, true);

            $existing = DB::table('weekly_shifts')
                ->where('schedual_id', $scheduleId)
                ->where('day', $day)
                ->first();

            $values = [
                'time_in'  => $isOff ? null : $timeIn,
                'time_out' => $isOff ? null : $timeOut,
                'is_off'   => $isOff ? 1 : 0,
                'active'   => 1,
                'updated_at' => now(),
            ];

            if ($existing) {
                DB::table('weekly_shifts')->where('id', $existing->id)->update($values);
            } else {
                $values['schedual_id'] = $scheduleId;
                $values['day'] = $day;
                $values['created_at'] = now();
                DB::table('weekly_shifts')->insert($values);
            }
        }
    }

    /**
     * Normalize any strtotime()-parseable time string into MySQL's
     * TIME column format (H:i:s). Returns null for blank/unparseable
     * input rather than storing "00:00:00" by accident.
     */
    private function toTimeOnly($value)
    {
        if (empty($value)) {
            return null;
        }

        $timestamp = strtotime($value);

        return $timestamp !== false ? date('H:i:s', $timestamp) : null;
    }
}