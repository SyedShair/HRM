<?php

// namespace App\Http\Controllers\Admin;

// use DB;
// use Carbon\Carbon;
// use App\Classes\table;
// use App\Classes\permission;
// use Illuminate\Http\Request;
// use App\Http\Controllers\Controller;
// use Barryvdh\DomPDF\Facade\Pdf;

// class SchedulesController extends Controller
// {
//     private const DAYS = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

//     /**
//      * GET /schedules - route name('schedule') points at index(); the real
    //  * logic lives in rota() so both /schedules and /staff-rota render the
    //  * same employee list, filterable by ?company_id=.
    //  */
    // public function index(Request $request)
    // {
    //     return $this->rota($request);
    // }

    // /**
    //  * GET /staff-rota
    //  * Employee list with current-or-upcoming schedule attached,
    //  * filterable by ?company_id=.
    //  */
    // public function rota(Request $request)
    // {
    //     if (permission::permitted('schedules-add') == 'fail') { return redirect()->route('denied'); }

    //     $companies = table::company()->orderBy('company')->get();

    //     $companyId = $request->query('company_id');
    //     $companyId = ($companyId !== null && is_numeric($companyId)) ? (int) $companyId : null;

    //     if (!$companyId && $companies->isNotEmpty()) {
    //         $companyId = $companies->first()->id;
    //     }

    //     $employees = table::people()
    //         ->join('tbl_company_data', 'tbl_people.id', '=', 'tbl_company_data.reference')
    //         ->where('tbl_people.employmentstatus', 'Active')
    //         ->when($companyId, fn ($q) => $q->where('tbl_company_data.company_id', $companyId))
    //         ->orderBy('tbl_people.lastname')
    //         ->get([
    //             'tbl_people.id', 'tbl_people.firstname', 'tbl_people.lastname', 'tbl_people.avatar',
    //             'tbl_company_data.jobposition', 'tbl_company_data.idno',
    //         ]);

    //     $today = date('Y-m-d');

    //     // FIX: previously this query only ever fetched non-archived
    //     // (archive = '0') schedules, which meant the instant a schedule
    //     // was archived it vanished from this list completely - with no
    //     // way to see it, print it, or permanently delete it again. We
    //     // now pull every schedule per employee (archived or not) and
    //     // decide which one to surface per row below: current > upcoming
    //     // > most recently archived.
    //     $schedules = table::schedules()
    //         ->whereIn('reference', $employees->pluck('id'))
    //         ->orderBy('datefrom', 'desc')
    //         ->get()
    //         ->groupBy('reference');

    //     $employees->transform(function ($emp) use ($schedules, $today) {
    //         $empSchedules = $schedules->get($emp->id, collect());
    //         $activeSet = $empSchedules->where('archive', '0');

    //         $emp->currentSchedule = $activeSet->first(function ($s) use ($today) {
    //             return $s->datefrom <= $today && $s->dateto >= $today;
    //         });

    //         $emp->nextSchedule = $emp->currentSchedule
    //             ? null
    //             : $activeSet->filter(fn ($s) => $s->datefrom > $today)->sortBy('datefrom')->first();

    //         // Only surface an archived schedule when there's nothing
    //         // live or upcoming to show instead, so an employee who
    //         // already has an active rota doesn't get cluttered with old
    //         // archived ones sitting alongside it.
    //         $emp->archivedSchedule = (!$emp->currentSchedule && !$emp->nextSchedule)
    //             ? $empSchedules->where('archive', '1')->sortByDesc('datefrom')->first()
    //             : null;

    //         return $emp;
    //     });

    //     return view('admin.schedules.index', compact('employees', 'companies', 'companyId'));
    // }

    /**
     * GET /schedules/new/{employeeId}
     */
//     public function create($employeeId)
//     {
//         if (permission::permitted('schedules-add') == 'fail') { return redirect()->route('denied'); }

//         $employee = table::people()->where('id', $employeeId)->first();

//         if (!$employee) {
//             return redirect('staff-rota')->with('error', trans('Employee not found.'));
//         }

//         $companyData = table::companydata()->where('reference', $employeeId)->first();
//         $days = self::DAYS;

//         return view('admin.schedules.form', compact('employee', 'companyData', 'days'));
//     }

//     /**
//      * POST /schedules/add
//      */
//     public function add(Request $request)
//     {
//         if (permission::permitted('schedules-add') == 'fail') { return redirect()->route('denied'); }

//         $request->validate([
//             'reference'    => 'required|integer|exists:tbl_people,id',
//             'datefrom'     => 'required|date',
//             'dateto'       => 'required|date|after_or_equal:datefrom',
//             'weekly_hours' => 'required|numeric|min:0|max:168',
//             'is_off'       => 'nullable|array',
//             'time_in'      => 'nullable|array',
//             'time_in.*'    => 'nullable|date_format:H:i',
//             'time_out'     => 'nullable|array',
//             'time_out.*'   => 'nullable|date_format:H:i',
//         ]);

//         $employee = table::people()->where('id', $request->reference)->first();

//         if (!$employee) {
//             return redirect('staff-rota')->withInput()->with('error', trans('Employee not found.'));
//         }

//         $companyData = table::companydata()->where('reference', $request->reference)->first();

//         $overlap = table::schedules()
//             ->where('reference', $request->reference)
//             ->where('archive', '0')
//             ->where('datefrom', '<=', $request->dateto)
//             ->where('dateto', '>=', $request->datefrom)
//             ->exists();

//         if ($overlap) {
//             return back()->withInput()->with('error', trans('This employee already has a schedule that overlaps these dates.'));
//         }

//         $totalHours = $this->calculateScheduledHours($request);
//         $weeklyHoursAllowed = (float) $request->weekly_hours;

//         if ($totalHours > $weeklyHoursAllowed + 0.001) {
//             return back()->withInput()->with('error', trans(
//                 'The scheduled hours (:total) exceed the weekly hours allowed (:allowed). Please adjust the daily times so the total does not go over the limit.',
//                 ['total' => number_format($totalHours, 2), 'allowed' => number_format($weeklyHoursAllowed, 2)]
//             ));
//         }

//         try {
//             DB::transaction(function () use ($request, $employee, $companyData, $weeklyHoursAllowed) {
//                 $this->saveSchedule($request, $employee, $companyData, $weeklyHoursAllowed);
//             });
//         } catch (\Exception $e) {
//             \Log::error('Failed to create schedule: '.$e->getMessage());
//             return back()->withInput()->with('error', trans('Something went wrong while saving this schedule. Please try again.'));
//         }

//         return redirect('staff-rota')->with('success', trans('Weekly schedule has been created!'));
//     }

//     /**
//      * GET /schedules/edit/{id}
//      */
//     public function edit($id)
//     {
//         if (permission::permitted('schedules-edit') == 'fail') { return redirect()->route('denied'); }

//         $schedule = table::schedules()->where('id', $id)->first();

//         if (!$schedule) {
//             return redirect('staff-rota')->with('error', trans('Schedule not found.'));
//         }

//         $employee = table::people()->where('id', $schedule->reference)->first();
//         $companyData = table::companydata()->where('reference', $schedule->reference)->first();

//         $shifts = table::weeklyshifts()
//             ->where('schedual_id', $schedule->id)
//             ->get()
//             ->keyBy('day');

//         $days = self::DAYS;

//         return view('admin.schedules.form', compact('employee', 'companyData', 'days', 'schedule', 'shifts'));
//     }

//     /**
//      * POST /schedules/update - id comes from a hidden field, not the URL.
//      */
//     public function update(Request $request)
//     {
//         if (permission::permitted('schedules-edit') == 'fail') { return redirect()->route('denied'); }

//         $request->validate([
//             'id'           => 'required|integer|exists:tbl_people_schedules,id',
//             'datefrom'     => 'required|date',
//             'dateto'       => 'required|date|after_or_equal:datefrom',
//             'weekly_hours' => 'required|numeric|min:0|max:168',
//             'is_off'       => 'nullable|array',
//             'time_in'      => 'nullable|array',
//             'time_in.*'    => 'nullable|date_format:H:i',
//             'time_out'     => 'nullable|array',
//             'time_out.*'   => 'nullable|date_format:H:i',
//         ]);

//         $schedule = table::schedules()->where('id', $request->id)->first();

//         if (!$schedule) {
//             return redirect('staff-rota')->with('error', trans('Schedule not found.'));
//         }

//         $employee = table::people()->where('id', $schedule->reference)->first();
//         $companyData = table::companydata()->where('reference', $schedule->reference)->first();

//         $overlap = table::schedules()
//             ->where('reference', $schedule->reference)
//             ->where('archive', '0')
//             ->where('id', '!=', $schedule->id)
//             ->where('datefrom', '<=', $request->dateto)
//             ->where('dateto', '>=', $request->datefrom)
//             ->exists();

//         if ($overlap) {
//             return back()->withInput()->with('error', trans('This employee already has another schedule that overlaps these dates.'));
//         }

//         $totalHours = $this->calculateScheduledHours($request);
//         $weeklyHoursAllowed = (float) $request->weekly_hours;

//         if ($totalHours > $weeklyHoursAllowed + 0.001) {
//             return back()->withInput()->with('error', trans(
//                 'The scheduled hours (:total) exceed the weekly hours allowed (:allowed). The rota was not updated - please adjust the daily times.',
//                 ['total' => number_format($totalHours, 2), 'allowed' => number_format($weeklyHoursAllowed, 2)]
//             ));
//         }

//         try {
//             DB::transaction(function () use ($request, $employee, $companyData, $schedule, $weeklyHoursAllowed) {
//                 $this->saveSchedule($request, $employee, $companyData, $weeklyHoursAllowed, $schedule);
//             });
//         } catch (\Exception $e) {
//             \Log::error('Failed to update schedule #'.$request->id.': '.$e->getMessage());
//             return back()->withInput()->with('error', trans('Something went wrong while updating this schedule. Please try again.'));
//         }

//         return redirect('staff-rota')->with('success', trans('Weekly schedule has been updated!'));
//     }

//     /**
//      * GET /schedules/archive/{id}
//      */
//     public function archive($id)
//     {
//         if (permission::permitted('schedules-archive') == 'fail') { return redirect()->route('denied'); }

//         table::schedules()->where('id', $id)->update(['archive' => '1']);
//         table::weeklyshifts()->where('schedual_id', $id)->update(['active' => 0]);

//         return redirect('staff-rota')->with('success', trans('Schedule has been archived.'));
//     }

//     /**
//      * GET /schedules/delete/{id}
//      * Permanent delete - used both for direct deletes and for the
//      * "Delete" action shown on the Rota page against an archived
//      * schedule (see admin.schedules.index).
//      */
//     public function delete($id)
//     {
//         if (permission::permitted('schedules-delete') == 'fail') { return redirect()->route('denied'); }

//         table::weeklyshifts()->where('schedual_id', $id)->delete();
//         table::schedules()->where('id', $id)->delete();

//         return redirect('staff-rota')->with('success', trans('Schedule has been deleted.'));
//     }

//     /**
//      * GET /rota/pdf/{id}
//      * Printable single-employee weekly schedule, viewable inline (not
//      * force-downloaded). $id is a tbl_people_schedules row id, same as
//      * edit()/archive()/delete() above - works for active, upcoming, or
//      * archived schedules alike.
//      */
//     public function todayShifts()
// {
//     $todayDay  = now()->format('l');
//     $todayDate = now()->format('Y-m-d');

//     $employees = DB::table('tbl_people')->get();

//     $shifts = DB::table('tbl_people_schedules as s')
//         ->join('weekly_shifts as w', 's.id', '=', 'w.schedual_id')
//         ->where('w.day', $todayDay)
//         ->where('w.active', 1)
//         ->where(function($q) use ($todayDate) {
//             $q->whereDate('s.datefrom', '<=', $todayDate)
//               ->where(function($q2) use ($todayDate) {
//                   $q2->whereDate('s.dateto', '>=', $todayDate)
//                      ->orWhereNull('s.dateto');
//               });
//         })
//         ->select('s.employee','w.day','w.time_in','w.time_out','s.reference')
//         ->get();

//     // ✅ ADD THIS
//    $todayAttendance = DB::table('tbl_people_attendance')
//     ->where('date', $todayDate)->get();
//     return view('today_shift', compact(
//         'shifts',
//         'todayDay',
//         'todayDate',
//         'employees',
//         'todayAttendance'
//     ));
// }
//     public function rotaPdf($id)
//     {
//         if (permission::permitted('schedules-add') == 'fail') { return redirect()->route('denied'); }

//         $schedule = table::schedules()->where('id', $id)->first();

//         if (!$schedule) {
//             return redirect('staff-rota')->with('error', trans('Schedule not found.'));
//         }

//         $employee = table::people()->where('id', $schedule->reference)->first();
//         $companyData = table::companydata()->where('reference', $schedule->reference)->first();

//         $shifts = table::weeklyshifts()
//             ->where('schedual_id', $schedule->id)
//             ->get()
//             ->keyBy('day');

//         $days = self::DAYS;

//         $pdf = Pdf::loadView('admin.schedules.pdf', compact('employee', 'companyData', 'schedule', 'shifts', 'days'))
//             ->setPaper('a4', 'portrait');

//         $filename = mb_strtoupper($employee->firstname.'-'.$employee->lastname.'-weekly-schedule').'.pdf';

//         return $pdf->stream($filename);
//     }

//     /**
//      * Sums scheduled hours across all non-off days from the submitted
//      * form. Shared by add() and update() so the cap is enforced identically.
//      */
//     private function calculateScheduledHours(Request $request): float
//     {
//         $isOff = $request->input('is_off', []);
//         $timeIn = $request->input('time_in', []);
//         $timeOut = $request->input('time_out', []);

//         $totalMinutes = 0;

//         foreach (self::DAYS as $day) {
//             if (in_array($day, $isOff)) {
//                 continue;
//             }

//             $in = $timeIn[$day] ?? null;
//             $out = $timeOut[$day] ?? null;

//             if ($in && $out) {
//                 $minutes = (strtotime($out) - strtotime($in)) / 60;
//                 if ($minutes < 0) {
//                     $minutes += 24 * 60; // overnight shift
//                 }
//                 $totalMinutes += $minutes;
//             }
//         }

//         return round($totalMinutes / 60, 2);
//     }

//     /**
//      * Writes the tbl_people_schedules summary row and its 7
//      * weekly_shifts detail rows. hours = the weekly hours ALLOWED
//      * figure the user entered (the contracted target), not a computed total.
//      */
//     private function saveSchedule(Request $request, $employee, $companyData, float $weeklyHoursAllowed, $existingSchedule = null)
//     {
//         $isOff = $request->input('is_off', []);
//         $timeIn = $request->input('time_in', []);
//         $timeOut = $request->input('time_out', []);

//         $restDays = [];
//         $firstWorkingTimeIn = null;
//         $firstWorkingTimeOut = null;

//         foreach (self::DAYS as $day) {
//             if (in_array($day, $isOff)) {
//                 $restDays[] = $day;
//                 continue;
//             }

//             $in = $timeIn[$day] ?? null;
//             $out = $timeOut[$day] ?? null;

//             if ($in && $out && $firstWorkingTimeIn === null) {
//                 $firstWorkingTimeIn = $in;
//                 $firstWorkingTimeOut = $out;
//             }
//         }

//         $employeeName = mb_strtoupper($employee->lastname.', '.$employee->firstname);
//         $idno = $companyData->idno ?? null;

//         $scheduleAttributes = [
//             'reference' => $employee->id,
//             'idno'      => $idno,
//             'employee'  => $employeeName,
//             'intime'    => $firstWorkingTimeIn,
//             'outime'    => $firstWorkingTimeOut,
//             'datefrom'  => $request->datefrom,
//             'dateto'    => $request->dateto,
//             'hours'     => (string) $weeklyHoursAllowed,
//             'restday'   => implode(',', $restDays),
//             'archive'   => '0',
//         ];

//         if ($existingSchedule) {
//             table::schedules()->where('id', $existingSchedule->id)->update($scheduleAttributes);
//             $scheduleId = $existingSchedule->id;
//             table::weeklyshifts()->where('schedual_id', $scheduleId)->delete();
//         } else {
//             $scheduleId = table::schedules()->insertGetId($scheduleAttributes);
//         }

//         $shiftRows = [];
//         foreach (self::DAYS as $day) {
//             $dayOff = in_array($day, $isOff);
//             $in = !$dayOff ? ($timeIn[$day] ?? null) : null;
//             $out = !$dayOff ? ($timeOut[$day] ?? null) : null;

//             $shiftRows[] = [
//                 'schedual_id' => $scheduleId,
//                 'day'         => $day,
//                 'time_in'     => $in ?: null,
//                 'time_out'    => $out ?: null,
//                 'is_off'      => $dayOff ? 1 : 0,
//                 'active'      => 1,
//                 'created_at'  => now(),
//                 'updated_at'  => now(),
//             ];
//         }

//         table::weeklyshifts()->insert($shiftRows);
//     }

    
// }



namespace App\Http\Controllers\Admin;

use DB;
use Carbon\Carbon;
use App\Classes\table;
use App\Classes\permission;
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

        try {
            DB::transaction(function () use ($request, $employee, $companyData, $weeklyHoursAllowed) {
                $this->saveSchedule($request, $employee, $companyData, $weeklyHoursAllowed);
            });
        } catch (\Exception $e) {
            \Log::error('Failed to create schedule: '.$e->getMessage());
            return back()->withInput()->with('error', trans('Something went wrong while saving this schedule. Please try again.'));
        }

        return redirect('staff-rota')->with('success', trans('Weekly schedule has been created!'));
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

        try {
            DB::transaction(function () use ($request, $employee, $companyData, $schedule, $weeklyHoursAllowed) {
                $this->saveSchedule($request, $employee, $companyData, $weeklyHoursAllowed, $schedule);
            });
        } catch (\Exception $e) {
            \Log::error('Failed to update schedule #'.$request->id.': '.$e->getMessage());
            return back()->withInput()->with('error', trans('Something went wrong while updating this schedule. Please try again.'));
        }

        return redirect('staff-rota')->with('success', trans('Weekly schedule has been updated!'));
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
     */
    public function todayShifts()
    {
        if (permission::permitted('schedules-add') == 'fail') { return redirect()->route('denied'); }

        // Anchor everything "today" to a single Carbon instance in the
        // app's configured timezone, so the day name, the date, and the
        // "is this shift running right now" check in the view can never
        // disagree with each other.
        $now = Carbon::now(config('app.timezone'));
        $todayDay  = $now->format('l');
        $todayDate = $now->format('Y-m-d');
        $nowTime   = $now->format('H:i'); // matches the H:i format time_in/time_out are stored/validated in

        $shifts = DB::table('tbl_people_schedules as s')
            ->join('weekly_shifts as w', 's.id', '=', 'w.schedual_id')
            ->join('tbl_people as p', 'p.id', '=', 's.reference')
            ->where('w.day', $todayDay)
            ->where('w.active', 1)
            // Archived schedules must never surface on today's shift
            // board - see fix note #1 above.
            ->where('s.archive', '0')
            // Only currently-active employees - see fix note #2 above.
            ->where('p.employmentstatus', 'Active')
            ->where(function ($q) use ($todayDate) {
                $q->whereDate('s.datefrom', '<=', $todayDate)
                  ->where(function ($q2) use ($todayDate) {
                      $q2->whereDate('s.dateto', '>=', $todayDate)
                         ->orWhereNull('s.dateto');
                  });
            })
            ->select('s.employee', 'w.day', 'w.time_in', 'w.time_out', 's.reference')
            ->distinct()
            ->orderBy('s.employee')
            ->get();

        $todayAttendance = DB::table('tbl_people_attendance')
            ->where('date', $todayDate)
            ->get();

        // Resolved once here instead of re-querying per row in the
        // blade - see fix note #4 above.
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
            'todayAttendance'
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
     */
    private function saveSchedule(Request $request, $employee, $companyData, float $weeklyHoursAllowed, $existingSchedule = null)
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
    }
}