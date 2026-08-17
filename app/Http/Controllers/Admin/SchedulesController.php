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
    public function index() 
    {
        if (permission::permitted('schedules')=='fail'){ return redirect()->route('denied'); }
        
        $employee = table::people()->get();
        $schedules = table::schedules()->get();
        $tf = table::settings()->value("time_format");
    
        return view('admin.schedules', compact('employee', 'schedules', 'tf'));
    }

    public function add(Request $request) 
    {
        if (permission::permitted('schedules-add')=='fail'){ return redirect()->route('denied'); }

        $v = $request->validate([
            'id' => 'required|max:20',
            'employee' => 'required|max:100',
            'datefrom' => 'required|date|max:15',
            'dateto' => 'required|date|max:15',
            'hours' => 'required|max:6',
            'restday' => 'required|max:155',
        ]);

    	$id = $request->id;
		$employee = mb_strtoupper($request->employee);
        $intime = date("h:i A", strtotime($request->intime)) ;
        $outime = date("h:i A", strtotime($request->outime)) ;
		$datefrom = $request->datefrom;
		$dateto = $request->dateto;
		$hours = $request->hours;
        $restday = ($request->restday != null) ? implode(', ', $request->restday) : null ;
        
        $ref = table::schedules()->where([['reference', $id],['archive', 0]])->exists();

        if ($ref == 1) 
        {
            return redirect('schedules')->with('error', trans("Oops! This employee has schedule already. Please arhive the present schedule to add new schedule."));
        }

        $emp_id = table::companydata()->where('reference', $id)->value('idno');
    
      $weekly =  table::schedules()->where('id', $id)->insertGetId([
        	'reference' => $id,
        	'idno' => $emp_id,
        	'employee' => $employee,
        	
        	'datefrom' => $datefrom, 
        	'dateto' => $dateto,
        	'hours' => $hours,
        	'restday' => $restday,
        	'archive' => '0',
    	]);

    	return redirect('schedules')->with('success', trans("New Schedule Added!"));
	}

    public function edit($id, Request $request) 
    {
        if (permission::permitted('schedules-edit')=='fail'){ return redirect()->route('denied'); }

        $s = table::schedules()->where('id', $id)->first();
        $r = explode(', ', $s->restday);
        $e_id = ($s->id == null) ? 0 : Crypt::encryptString($s->id) ;
        $tf = table::settings()->value("time_format");
        
        return view('admin.edits.edit-schedule', compact('s','r', 'e_id', 'tf'));
    }

    public function update(Request $request) 
    {
        if (permission::permitted('schedules-edit')=='fail'){ return redirect()->route('denied'); }

        $v = $request->validate([
            'id' => 'required|max:200',
            'intime' => 'required|max:15',
            'outime' => 'required|max:15',
            'datefrom' => 'required|date|max:15',
            'dateto' => 'required|date|max:15',
            'hours' => 'required|max:6',
            'restday' => 'required|max:155',
        ]);

        $id = Crypt::decryptString($request->id);
        $intime = date("h:i A", strtotime($request->intime)) ;
        $outime = date("h:i A", strtotime($request->outime)) ;
        $datefrom = $request->datefrom; 
        $dateto = $request->dateto; 
        $hours = $request->hours;
        $restday = implode(', ', $request->restday);

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

        return redirect('schedules')->with('success', trans("Schedule has been updated!"));
    }

    public function delete($id, Request $request) 
    {
        if (permission::permitted('schedules-delete')=='fail'){ return redirect()->route('denied'); }

        table::schedules()->where('id', $id)->delete();

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
     * ðŸ”¹ GET WEEKLY DATA (for modal)
     */
   public function getWeekly($id)
{
    // get schedule
    $schedule = DB::table('tbl_people_schedules')
        ->where('id', $id)
        ->first();

    if (!$schedule) {
        return response()->json([
            'restDays' => [],
            'shifts' => []
        ]);
    }

    // convert restday string â†’ array
    $restDays = [];
    if (!empty($schedule->restday)) {
        $restDays = array_map('trim', explode(',', $schedule->restday));
    }

    // get shifts and group by day (VERY IMPORTANT for frontend)
    $shifts = DB::table('weekly_shifts')
        ->where('schedual_id', $id) // keep your DB column name
        ->where('active', 1)
        ->get()
        ->keyBy('day'); // ðŸ‘ˆ this makes it easy in JS

    return response()->json([
        'restDays' => $restDays,
        'shifts'   => $shifts
    ]);
}

   	
 public function storeWeekly(Request $request)
{
    $scheduleId = $request->schedule_id;

    $schedule = DB::table('tbl_people_schedules')
        ->where('id', $scheduleId)
        ->first();

    if (!$schedule) {
        return back()->with('error', 'Schedule not found');
    }

    // rest days
    $restDays = [];

    if (!empty($schedule->restday)) {
        $restDays = array_map('trim', explode(',', $schedule->restday));
    }

    $shifts = $request->shift ?? [];
    if (empty($shifts)) {
        return back()->with('error', 'No shift data provided');
    }
    foreach ($shifts as $day => $time) {

    //     $timeIn  = $time['in'] ?? null;
    //     $timeOut = $time['out'] ?? null;

    //     // skip empty rows
    //     if (!$timeIn || !$timeOut) {
    //         continue;
    //     }

    //     $isOff = in_array($day, $restDays) ? 1 : 0;
    //   DB::table('weekly_shifts')->updateOrInsert(
    //         [
    //             'schedual_id' => $scheduleId,
    //             'day'         => $day,
    //         ],
    //         [
    //             'time_in'     => $timeIn,
    //             'time_out'    => $timeOut,
    //             'is_off'      => $isOff,
    //             'active'      => 1,
               
    //         ]
    //     );
    
    $timeIn  = $time['in'] ?? null;
$timeOut = $time['out'] ?? null;

$isOff = in_array($day, $restDays) || !$timeIn || !$timeOut;

DB::table('weekly_shifts')->updateOrInsert(
    [
        'schedual_id' => $scheduleId,
        'day'         => $day,
    ],
    [
        'time_in'  => $isOff ? null : $timeIn,
        'time_out' => $isOff ? null : $timeOut,
        'is_off'   => $isOff ? 1 : 0,
        'active'   => 1,
    ]
);
    }

    return back()->with('success', 'Weekly rota saved successfully');
}


public function pdf($id)
{
   
    // schedule
    $schedule = DB::table('tbl_people_schedules')
        ->where('id', $id)
        ->first();

    if (!$schedule) {
        return back()->with('error', 'Schedule not found');
    }

    // weekly shifts
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

// public function todayShifts()
//     {
        
        
//       $todayDay  = now()->format('l');
// $todayDate = now()->format('Y-m-d');
//  $employees = DB::table('tbl_people')->get();

// $shifts = DB::table('tbl_people_schedules as s')
//     ->join('weekly_shifts as w', 's.id', '=', 'w.schedual_id')

//     ->where('w.day', $todayDay)
//     ->where('w.active', 1)

//     ->where(function($q) use ($todayDate) {
//         $q->whereDate('s.datefrom', '<=', $todayDate)
//           ->where(function($q2) use ($todayDate) {
//               $q2->whereDate('s.dateto', '>=', $todayDate)
//                  ->orWhereNull('s.dateto');
//           });
//     })

//     ->select('s.employee','w.day','w.time_in','w.time_out')
//     ->get();
    
//         return view('today_shift', compact('shifts', 'todayDay', 'todayDate','employees'));
//     }


public function todayShifts()
{
    $todayDay  = now()->format('l');
    $todayDate = now()->format('Y-m-d');

    $employees = DB::table('tbl_people')->get();

    $shifts = DB::table('tbl_people_schedules as s')
        ->join('weekly_shifts as w', 's.id', '=', 'w.schedual_id')
        ->where('w.day', $todayDay)
        ->where('w.active', 1)
        ->where(function($q) use ($todayDate) {
            $q->whereDate('s.datefrom', '<=', $todayDate)
              ->where(function($q2) use ($todayDate) {
                  $q2->whereDate('s.dateto', '>=', $todayDate)
                     ->orWhereNull('s.dateto');
              });
        })
        ->select('s.employee','w.day','w.time_in','w.time_out','s.reference')
        ->get();

    // ✅ ADD THIS
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
    $days = [
        'Monday',
        'Tuesday',
        'Wednesday',
        'Thursday',
        'Friday',
        'Saturday',
        'Sunday',
    ];

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
        'days' => $days,
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

}
