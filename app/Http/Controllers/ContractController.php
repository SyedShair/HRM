<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class ContractController extends Controller
{
    public function printContract($employee_id)
    {
        // Fetch employee
        $employee = DB::table('tbl_people')->where('id', $employee_id)->first();
        if (!$employee) abort(404, 'Employee not found');

        // Fetch job
        $job = DB::table('tbl_company_data')->where('reference', $employee_id)->first();
        if (!$job) abort(404, 'Employment record not found');

        // Fetch company
        $company = DB::table('tbl_form_company')->first();
        if (!$company) abort(404, 'Company not found');

        // Fetch schedule
        $schedule = DB::table('tbl_people_schedules')->where('reference', $employee_id)->first();

        // Fetch leave group
        $leaveGroup = DB::table('tbl_form_leavegroup')->where('id', $job->leaveprivilege ?? 0)->first();

        // Working hours & salary
        $weeklyHours = $schedule->hours ?? 37.5;        // Default 37.5 hours/week
        $hourlyRate  = $employee->perhourpay ?? 0;      // Hourly rate

        $yearlyHours  = $weeklyHours * 52;             // Total yearly hours
        $annualSalary = $employee->accountpay * 12; 
        $monthlySalary = $employee->accountpay;

        // Pass all data to PDF view
        $pdf = Pdf::loadView('Employment_Contract', [
            'employee'      => $employee,
            'job'           => $job,
            'schedule' => $schedule ,
            'company'       => $company,
            'weeklyHours'   => $weeklyHours,
            'hourlyRate'    => number_format($hourlyRate, 2),
            'yearlyHours'   => $yearlyHours,
            'annualSalary'  => number_format($annualSalary, 2),
            'monthlySalary' => number_format($monthlySalary, 2),
            'leaveGroup'    => $leaveGroup,
        ])->setPaper('A4', 'portrait');

        return $pdf->stream("Employment_Contract_{$employee->firstname}_{$employee->lastname}.pdf");
    }
}
