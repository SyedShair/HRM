<?php
namespace App\Http\Controllers\Admin;
use DB;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use App\Classes\table;
use App\Classes\permission;
use App\Http\Requests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use App\Http\Controllers\Controller;
use App\Traits\AttendanceShiftHelpers;

class AttendanceController extends Controller
{
    use AttendanceShiftHelpers;

    public function index()
    {
        if (permission::permitted('attendance')=='fail'){ return redirect()->route('denied'); }

        $data = table::attendance()->orderBy('date', 'desc')->take(60)->get();
        $ss = table::settings()->select('clock_comment', 'time_format')->first();
        $employees = table::people()->get();
        $tf = table::settings()->value("time_format");
        $cc = table::settings()->value("clock_comment");

        return view('admin.attendance', compact('data', 'ss', 'employees', 'tf', 'cc'));
    }

    public function clock()
    {
        return view('clock');
    }

    public function edit($id, Request $request)
    {
        if (permission::permitted('attendance-edit')=='fail'){ return redirect()->route('denied'); }

        $a = table::attendance()->where('id', $id)->first();
        $e_id = ($a->id == null) ? 0 : Crypt::encryptString($a->id) ;
        $tf = table::settings()->value("time_format");

        return view('admin.edits.edit-attendance', compact('a', 'e_id', 'tf'));
    }

    public function delete($id, Request $request)
    {
        if (permission::permitted('attendance-delete')=='fail'){ return redirect()->route('denied'); }

        $id = $request->id;
        $find = table::attendance()->where('id', $id)->first();

        // Delete existing salary record for the same day (optional to avoid duplicates)
        DB::table('daily_salaries')->where([
            ['idno', '=', $find->idno],
            ['date', '=', $find->date],
        ])->delete();

        table::attendance()->where('id', $id)->delete();

        return redirect('attendance')->with('success', trans("Deleted!"));
    }

    public function update(Request $request)
    {
        if (permission::permitted('attendance-edit')=='fail') { return redirect()->route('denied'); }

        $v = $request->validate([
            'id' => 'required|max:200',
            'idno' => 'required|max:100',
            'timein' => 'required|max:15',
            'timeout' => 'required|max:15',
            'reason' => 'required|max:255',
        ]);

        $id = Crypt::decryptString($request->id);
        $idno = $request->idno;

        // Canonical storage format: Y-m-d H:i:s (24-hour), parsed through
        // the org timezone so it can never drift from what ClockController
        // or addEntry() store. The time_format setting only affects how
        // times are *displayed*, never how they're stored or compared.
        $timeIN = $this->parseInOrgTz($request->timein_date." ".$request->timein);
        $timeOUT = $this->parseInOrgTz($request->timeout_date." ".$request->timeout);
        $reason = $request->reason;

        // The shift that applies is keyed off the clock-in date's day of
        // week (an overnight shift is still "that day's" shift even
        // though timeout rolls into the next calendar day).
        $shiftDate = $this->parseDateInOrgTz($request->timein_date);
        $shift = $this->resolveShift($idno, $shiftDate);

        $actualIn = Carbon::createFromFormat("Y-m-d H:i:s", $timeIN);
        $actualOut = Carbon::createFromFormat("Y-m-d H:i:s", $timeOUT);

        $status_in = 'Ok';
        $status_out = 'Ok';
        $late_minutes = 0;
        $early_in_minutes = 0;
        $early_minutes = 0;
        $overtime_minutes = 0;
        $schedIn = null;

        if ($shift !== null && $shift['is_off']) {
            // Editing an entry that falls on what the rota now considers
            // a rest day - keep the record, just flag it, don't penalize.
            $status_in = 'Rest Day';
            $status_out = 'Rest Day';
        } elseif ($shift !== null) {
            $schedIn = Carbon::createFromFormat('Y-m-d H:i:s', $shiftDate.' '.$shift['time_in']);
            $schedOut = Carbon::createFromFormat('Y-m-d H:i:s', $shiftDate.' '.$shift['time_out']);

            $late = $this->lateStats($schedIn, $actualIn);
            $status_in = $late['status'];
            $late_minutes = $late['lateMinutes'];
            $early_in_minutes = $late['earlyMinutes'];

            $out = $this->outStats($schedIn, $schedOut, $actualIn, $actualOut);
            $status_out = $out['status'];
            $early_minutes = $out['earlyMinutes'];
            $overtime_minutes = $out['overtimeMinutes'];
        }
        // if $shift === null (no active schedule / no rota for that day at
        // all) statuses stay 'Ok' and minutes stay 0, same as before.

        // Total hours count from the scheduled start if they clocked in
        // early (status_in stays "Early In" for reporting either way).
        $t1 = $this->effectiveClockIn($schedIn, $actualIn);
        $t2 = $actualOut;
        if ($t2->lessThan($t1)) {
            $t2 = $t2->copy()->addDay();
        }
        $th = $t1->diffInHours($t2);
        $tm = floor(($t1->diffInMinutes($t2) - (60 * $th)));
        $totalhour = $th.".".$tm;

        table::attendance()->where('id', $id)->update([
            'timein' => $timeIN,
            'timeout' => $timeOUT,
            'reason' => $reason,
            'totalhours' => $totalhour,
            'status_timein' => $status_in,
            'status_timeout' => $status_out,
            'late_minutes' => $late_minutes,
            'early_in_minutes' => $early_in_minutes,
            'early_minutes' => $early_minutes,
            'overtime_minutes' => $overtime_minutes,
        ]);

        $find = table::attendance()->where('id', $id)->first();
        $emp = DB::table('tbl_people')->where('id', $find->reference)->first();

        // if ($emp && $emp->perhourpay) {
        //     $employee_id = $emp->id;
        //     $rate = $emp->perhourpay;
        //     $salary = number_format((float) $totalhour * $rate, 2, '.', '');
        //     $clockInDate = Carbon::createFromFormat('Y-m-d H:i:s', $timeIN)->format('Y-m-d');

        //     // Delete existing salary record for the same day (optional to avoid duplicates)
        //     DB::table('daily_salaries')->where([
        //         ['idno', '=', $idno],
        //         ['date', '=', $clockInDate],
        //     ])->delete();

        //     DB::table('daily_salaries')->updateOrInsert([
        //         'employee_id'   => $employee_id,
        //         'idno'          => $idno,
        //         'date'          => $clockInDate,
        //         'total_hours'   => $totalhour,
        //         'rate'          => $rate,
        //         'daily_salary'  => $salary,
        //         'status'        => 'Pending',
        //         'created_at'    => now(),
        //         'updated_at'    => now(),
        //     ]);
        // }

        return redirect('attendance')->with('success', trans("Employee attendance has been updated!"));
    }

    public function addEntry(Request $request)
    {

            
        if (permission::permitted('attendance')=='fail'){ return redirect()->route('denied'); }
        if ($request->ref == NULL) {
            return redirect('attendance')->with('error', trans("Please fill the form completely."));
        }

        $v = $request->validate([
            'ref' => 'required|max:250',
            'date' => 'required|max:15',
            'timein' => 'required|max:15',
            'timeout' => 'nullable|max:15',
        ]);

        $reference = $request->ref;
        $date = $this->parseDateInOrgTz($request->date);

        // Canonical storage format: 24-hour H:i:s, parsed through the org
        // timezone so it lines up exactly with the shift times coming
        // out of resolveShift(), and with what ClockController stores.
        $timein = $this->parseTimeInOrgTz($request->timein);
        $timeout = $this->parseTimeInOrgTz($request->timeout);
        $ip = $request->ip();
        $tf = table::settings()->value('time_format');

        // ip resriction
        $iprestriction = table::settings()->value('iprestriction');
        if ($iprestriction != NULL)
        {
            $ips = explode(",", $iprestriction);

            if(in_array($ip, $ips) == false)
            {
                return redirect('attendance')->with('error', trans("Whoops! You are not allowed to Clock In or Out from your IP address")." ".$ip);
            }
        }

        $emp_id = table::companydata()->where('id', $reference)->value('reference');
        $emp_idno = table::companydata()->where('id', $reference)->value('idno');

        if($emp_id == null || $emp_idno == null) {
            return redirect('attendance')->with('error', trans("Employee is not found."));
        }

        $emp = table::people()->where('id', $emp_id)->first();
        $lastname = $emp->lastname;
        $firstname = $emp->firstname;
        $mi = $emp->mi;
        $employee = mb_strtoupper($lastname.', '.$firstname.' '.$mi);

        $shift = $this->resolveShift($emp_idno, $date);

        if ($timeout == null)
        {
            $has = table::attendance()->where([['idno', $emp_idno],['date', $date]])->exists();

            if ($has == 1)
            {
                $hti = table::attendance()->where([['idno', $emp_idno],['date', $date]])->value('timein');
                $hti_24 = $this->formatForDisplay($hti, $tf);

                return redirect('attendance')->with('error', trans("The employee already clock in today at")." ".$hti_24);

            } else {

                $status_in = 'Ok';
                $late_minutes = 0;
                $early_in_minutes = 0;

                if ($shift !== null && $shift['is_off']) {
                    // Rest day, but they physically clocked in - record it
                    // and flag it rather than silently treating it as a
                    // normal working day.
                    $status_in = 'Rest Day';
                } elseif ($shift !== null) {
                    $schedIn = Carbon::createFromFormat('Y-m-d H:i:s', $date.' '.$shift['time_in']);
                    $actualIn = Carbon::createFromFormat('Y-m-d H:i:s', $date.' '.$timein);

                    $late = $this->lateStats($schedIn, $actualIn);
                    $status_in = $late['status'];
                    $late_minutes = $late['lateMinutes'];
                    $early_in_minutes = $late['earlyMinutes'];
                }

                table::attendance()->insert([
                    [
                        'idno' => $emp_idno,
                        'reference' => $emp_id,
                        'date' => $date,
                        'employee' => $employee,
                        'timein' => $date." ".$timein,
                        'status_timein' => $status_in,
                        'late_minutes' => $late_minutes,
                        'early_in_minutes' => $early_in_minutes,
                    ],
                ]);

                return redirect('attendance')->with('success', trans("Employee attendance has been added!"));
            }
        }

        if ($timeout != null && $timein != null)
        {
            $has = table::attendance()->where([['idno', $emp_idno],['date', $date]])->exists();

            if ($has == 1)
            {
                $hti = table::attendance()->where([['idno', $emp_idno],['date', $date]])->value('timein');
                $hti_24 = $this->formatForDisplay($hti, $tf);

                return redirect('attendance')->with('error', trans("The employee already clock in today at")." ".$hti_24);

            } else {

                $status_in = 'Ok';
                $status_out = 'Ok';
                $late_minutes = 0;
                $early_in_minutes = 0;
                $early_minutes = 0;
                $overtime_minutes = 0;
                $schedIn = null;

                $actualIn = Carbon::createFromFormat('Y-m-d H:i:s', $date.' '.$timein);
                $actualOut = Carbon::createFromFormat('Y-m-d H:i:s', $date.' '.$timeout);

                if ($shift !== null && $shift['is_off']) {
                    $status_in = 'Rest Day';
                    $status_out = 'Rest Day';
                } elseif ($shift !== null) {
                    $schedIn = Carbon::createFromFormat('Y-m-d H:i:s', $date.' '.$shift['time_in']);
                    $schedOut = Carbon::createFromFormat('Y-m-d H:i:s', $date.' '.$shift['time_out']);

                    $late = $this->lateStats($schedIn, $actualIn);
                    $status_in = $late['status'];
                    $late_minutes = $late['lateMinutes'];
                    $early_in_minutes = $late['earlyMinutes'];

                    $out = $this->outStats($schedIn, $schedOut, $actualIn, $actualOut);
                    $status_out = $out['status'];
                    $early_minutes = $out['earlyMinutes'];
                    $overtime_minutes = $out['overtimeMinutes'];
                }

                // Total hours count from the scheduled start time if they
                // clocked in early - status_in still reports "Early In".
                $time1 = $this->effectiveClockIn($schedIn, $actualIn);
                $time2 = $actualOut->copy();

                // Handle overnight shift
                if ($time2->lessThan($time1)) {
                    $time2->addDay();
                }

                // Proper decimal hours
                $totalMinutes = $time1->diffInMinutes($time2);
                $th = floor($totalMinutes / 60);
                $tm = $totalMinutes % 60;
                $totalhour = round($th + ($tm / 60), 2);

                table::attendance()->insert([
                    [
                        'idno' => $emp_idno,
                        'reference' => $emp_id,
                        'date' => $date,
                        'employee' => $employee,
                        'timein' => $date." ".$timein,
                        'status_timein' => $status_in,
                        'timeout' => $date." ".$timeout,
                        'totalhours' => $totalhour,
                        'status_timeout' => $status_out,
                        'late_minutes' => $late_minutes,
                        'early_in_minutes' => $early_in_minutes,
                        'early_minutes' => $early_minutes,
                        'overtime_minutes' => $overtime_minutes,
                    ],
                ]);

                return redirect('attendance')->with('success', trans("Employee attendance has been added!"));
            }
        }
    }

    /**
     * Bulk-fill missing attendance for one employee across a date range,
     * using their weekly rota (weekly_shifts) rather than a single flat
     * schedule.
     *
     * For each day in the range that doesn't already have a record:
     *   - Skipped (skippedNoSchedule) if the employee has no active
     *     schedule, or that schedule has no usable time for the day.
     *   - Skipped (skippedRestDay) if weekly_shifts marks that day of
     *     week as is_off for their schedule - no record is created for
     *     rest days.
     *   - Otherwise inserted using that day's specific time_in/time_out
     *     from weekly_shifts (or the schedule's flat intime/outime as a
     *     fallback if no weekly_shifts row exists for that day).
     *
     * Because the record is filled in using the scheduled times exactly,
     * late/early/overtime minutes are always 0 for auto-filled days -
     * there's no "actual" clock time to compare against.
     */
    public function markRange(Request $request)
    {
        if (permission::permitted('attendance')=='fail'){
            return response()->json(['error' => trans("You do not have permission to do this.")], 403);
        }

        $v = $request->validate([
            'emp_id' => 'required|max:100',
            'datefrom' => 'required|max:15',
            'dateto' => 'required|max:15',
        ]);

        $emp_idno = $request->emp_id;

        $dateFrom = Carbon::parse($request->datefrom)->startOfDay();
        $dateTo = Carbon::parse($request->dateto)->startOfDay();

        if ($dateTo->lessThan($dateFrom)) {
            return response()->json(['error' => trans("End date must be on or after the start date.")], 422);
        }

        $companyRow = table::companydata()->where('idno', $emp_idno)->first();
        $emp_id = $companyRow->reference ?? null;

        if ($emp_id == null) {
            return response()->json(['error' => trans("Employee is not found.")], 404);
        }

        $emp = table::people()->where('id', $emp_id)->first();

        if ($emp == null) {
            return response()->json(['error' => trans("Employee is not found.")], 404);
        }

        $employee = mb_strtoupper($emp->lastname.', '.$emp->firstname.' '.$emp->mi);

        $created = 0;
        $skippedExisting = 0;
        $skippedNoSchedule = 0;
        $skippedRestDay = 0;

        $period = CarbonPeriod::create($dateFrom, $dateTo);

        foreach ($period as $day) {
            $date = $day->format('Y-m-d');

            $has = table::attendance()->where([['idno', $emp_idno], ['date', $date]])->exists();

            if ($has) {
                $skippedExisting++;
                continue;
            }

            $shift = $this->resolveShift($emp_idno, $date);

            if ($shift === null) {
                $skippedNoSchedule++;
                continue;
            }

            if ($shift['is_off']) {
                $skippedRestDay++;
                continue;
            }

            // Canonical storage format: 24-hour H:i:s.
            $timein = $shift['time_in'];
            $timeout = $shift['time_out'];

            $status_in = 'In Time';
            $status_out = 'On Time';

            $time1 = Carbon::createFromFormat("Y-m-d H:i:s", $date." ".$timein);
            $time2 = Carbon::createFromFormat("Y-m-d H:i:s", $date." ".$timeout);

            if ($time2->lessThan($time1)) {
                $time2->addDay();
            }

            $totalMinutes = $time1->diffInMinutes($time2);
            $th = floor($totalMinutes / 60);
            $tm = $totalMinutes % 60;
            $totalhour = round($th + ($tm / 60), 2);

            table::attendance()->insert([
                [
                    'idno' => $emp_idno,
                    'reference' => $emp_id,
                    'date' => $date,
                    'employee' => $employee,
                    'timein' => $date." ".$timein,
                    'status_timein' => $status_in,
                    'timeout' => $date." ".$timeout,
                    'totalhours' => $totalhour,
                    'status_timeout' => $status_out,
                    'late_minutes' => 0,
                    'early_in_minutes' => 0,
                    'early_minutes' => 0,
                    'overtime_minutes' => 0,
                ],
            ]);

            $created++;
        }

        return response()->json([
            'created' => $created,
            'skippedExisting' => $skippedExisting,
            'skippedNoSchedule' => $skippedNoSchedule,
            'skippedRestDay' => $skippedRestDay,
        ]);
    }

    public function getFilter(Request $request)
    {
        if (permission::permitted('reports')=='fail'){ return redirect()->route('denied'); }

        $datefrom = $request->datefrom;
        $dateto = $request->dateto;

        if ($datefrom == null AND $dateto == null)
        {
            $data = table::attendance()->select('id', 'idno', 'date', 'employee', 'timein', 'timeout', 'totalhours', 'comment', 'status_timein', 'status_timeout', 'late_minutes', 'early_in_minutes', 'early_minutes', 'overtime_minutes')->get();
            return response()->json($data);
        }

        if ($datefrom !== null AND $dateto !== null)
        {
            $data = table::attendance()->whereBetween('date', [$datefrom, $dateto])->select('id', 'idno', 'date', 'employee', 'timein', 'timeout', 'totalhours', 'comment', 'status_timein', 'status_timeout', 'late_minutes', 'early_in_minutes', 'early_minutes', 'overtime_minutes')->get();
            return response()->json($data);
        }
    }
}