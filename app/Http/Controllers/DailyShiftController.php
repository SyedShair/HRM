<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DailyShiftController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status', 'Pending'); // Default tab
        $query = DB::table('daily_salaries')
            ->join('tbl_people', 'daily_salaries.employee_id', '=', 'tbl_people.id')
            ->select(
                'daily_salaries.*',
                'tbl_people.firstname',
                'tbl_people.lastname'
            )
            ->where('daily_salaries.status', $status);

        if ($request->filled('employee_id')) {
            $query->where('daily_salaries.employee_id', $request->employee_id);
        }

        if ($request->filled('from_date') && $request->filled('to_date')) {
            $query->whereBetween('daily_salaries.date', [$request->from_date, $request->to_date]);
        }

        $salaries = $query->orderBy('daily_salaries.date', 'asc')->get();
        $total = $salaries->sum('daily_salary');

        $employees = DB::table('tbl_people')
            ->where('employmentstatus', 'Active')
            ->get();

        return view('admin.dailyshift', compact('salaries', 'employees', 'status', 'total', 'request'));
    }

    public function updateRange(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|integer',
            'from_date' => 'required|date',
            'to_date' => 'required|date|after_or_equal:from_date',
            'status' => 'required|in:Paid,Pending',
        ]);

        DB::table('daily_salaries')
        ->where('employee_id', $request->employee_id)
        ->whereBetween('date', [$request->from_date, $request->to_date])
        ->update(['status' => $request->status]);

        return redirect()->back()->with('success', 'Salary status updated successfully.');
    }
}
