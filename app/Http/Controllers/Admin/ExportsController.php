<?php

namespace App\Http\Controllers\Admin;
use DB;
use App\Classes\table;
use App\Classes\permission;
use App\Http\Requests;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Storage;

class ExportsController extends Controller
{
		
public function company(Request $request) 
{
    if (permission::permitted('company') == 'fail') {
        return redirect()->route('denied');
    }

    $date = date('Y-m-d');
    $time = date('His');
    $fileName = "companies-{$date}T{$time}.csv";

    $companies = table::company()->get();

    // ✅ Stream download (no file saved)
    return response()->streamDownload(function () use ($companies) {

        $handle = fopen('php://output', 'w');

        // Header
        fputcsv($handle, ['ID', 'COMPANY']);

        // Data
        foreach ($companies as $c) {
            fputcsv($handle, [
                $c->id,
                $c->company
            ]);
        }

        fclose($handle);

    }, $fileName);
}
public function department(Request $request) 
{
    if (permission::permitted('departments') == 'fail') {
        return redirect()->route('denied');
    }

    $date = date('Y-m-d');
    $time = date('His');
    $fileName = "departments-{$date}T{$time}.csv";

    $departments = table::department()->get();

    return response()->streamDownload(function () use ($departments) {

        $handle = fopen('php://output', 'w');

        // ✅ Header
        fputcsv($handle, ['ID', 'DEPARTMENT']);

        // ✅ Data
        foreach ($departments as $d) {
            fputcsv($handle, [
                $d->id,
                $d->department
            ]);
        }

        fclose($handle);

    }, $fileName);
}

public function jobtitle(Request $request) 
{
    if (permission::permitted('jobtitles') == 'fail') {
        return redirect()->route('denied');
    }

    $date = date('Y-m-d');
    $time = date('His');
    $fileName = "jobtitles-{$date}T{$time}.csv";

    $jobtitles = table::jobtitle()->get();

    return response()->streamDownload(function () use ($jobtitles) {

        $handle = fopen('php://output', 'w');

        // ✅ Correct header
        fputcsv($handle, ['ID', 'JOB TITLE', 'DEPARTMENT CODE']);

        // ✅ Data
        foreach ($jobtitles as $j) {
            fputcsv($handle, [
                $j->id,
                $j->jobtitle,
                $j->dept_code
            ]);
        }

        fclose($handle);

    }, $fileName);
}
public function leavetypes(Request $request) 
{
    if (permission::permitted('leavetypes') == 'fail') {
        return redirect()->route('denied');
    }

    $date = date('Y-m-d');
    $time = date('His');
    $fileName = "leavetypes-{$date}T{$time}.csv";

    $leavetypes = table::leavetypes()->get();

    return response()->streamDownload(function () use ($leavetypes) {

        $handle = fopen('php://output', 'w');

        // ✅ Header
        fputcsv($handle, ['ID', 'LEAVE TYPE', 'LIMIT', 'PER CALENDAR']);

        // ✅ Data
        foreach ($leavetypes as $l) {
            fputcsv($handle, [
                $l->id,
                $l->leavetype,
                $l->limit,
                $l->percalendar
            ]);
        }

        fclose($handle);

    }, $fileName);
}

public function employeeList() 
{
    if (permission::permitted('reports') == 'fail') {
        return redirect()->route('denied');
    }

    $date = date('Y-m-d');
    $time = date('His');
    $fileName = "employee-lists-{$date}T{$time}.csv";

    $employees = table::people()->get();

    return response()->streamDownload(function () use ($employees) {

        $handle = fopen('php://output', 'w');

        // ✅ Header
        fputcsv($handle, [
            'ID',
            'EMPLOYEE',
            'AGE',
            'GENDER',
            'CIVIL STATUS',
            'MOBILE NUMBER',
            'EMAIL ADDRESS',
            'EMPLOYMENT TYPE',
            'EMPLOYMENT STATUS'
        ]);

        // ✅ Data
        foreach ($employees as $d) {

            fputcsv($handle, [
                $d->id,
                trim($d->lastname . ' ' . $d->firstname . ' ' . $d->mi),
                $d->age,
                $d->gender,
                $d->civilstatus,
                $d->mobileno,
                $d->emailaddress,
                $d->employmenttype,
                $d->employmentstatus
            ]);
        }

        fclose($handle);

    }, $fileName);
}
public function attendanceReport(Request $request) 
{
    if (permission::permitted('reports') == 'fail') {
        return redirect()->route('denied');
    }

    $id = $request->emp_id;
    $datefrom = $request->datefrom;
    $dateto = $request->dateto;

    // ✅ Build query once (NO duplication)
    $query = table::attendance();

    if ($id) {
        $query->where('idno', $id);
    }

    if ($datefrom && $dateto) {
        $query->whereBetween('date', [$datefrom, $dateto]);
    }

    $data = $query->get();

    if ($data->isEmpty()) {
        return redirect('reports/employee-attendance')
            ->with('error', 'No records found.');
    }

    $date = date('Y-m-d');
    $time = date('His');
    $fileName = "attendance-reports-{$date}T{$time}.csv";

    return response()->streamDownload(function () use ($data) {

        $handle = fopen('php://output', 'w');

        // ✅ Header
        fputcsv($handle, [
            'ID',
            'IDNO',
            'DATE',
            'EMPLOYEE',
            'TIME IN',
            'TIME OUT',
            'TOTAL HOURS',
            'STATUS IN',
            'STATUS OUT'
        ]);

        // ✅ Rows
        foreach ($data as $d) {
            fputcsv($handle, [
                $d->id,
                $d->idno,
                $d->date,
                $d->employee,
                $d->timein,
                $d->timeout,
                $d->totalhours,
                $d->status_timein,
                $d->status_timeout
            ]);
        }

        fclose($handle);

    }, $fileName);
}

public function leavesReport(Request $request) 
{
    if (permission::permitted('reports') == 'fail') {
        return redirect()->route('denied');
    }

    $id = $request->emp_id;
    $datefrom = $request->datefrom;
    $dateto = $request->dateto;

    // ✅ Build query once
    $query = table::leaves();

    if ($id) {
        $query->where('idno', $id);
    }

    if ($datefrom && $dateto) {
        $query->whereBetween('leavefrom', [$datefrom, $dateto]);
    }

    $data = $query->get();

    if ($data->isEmpty()) {
        return redirect('reports/employee-leaves')
            ->with('error', 'No records found.');
    }

    $date = date('Y-m-d');
    $time = date('His');
    $fileName = "leave-reports-{$date}T{$time}.csv";

    return response()->streamDownload(function () use ($data) {

        $handle = fopen('php://output', 'w');

        // ✅ Header
        fputcsv($handle, [
            'ID',
            'IDNO',
            'EMPLOYEE',
            'TYPE',
            'LEAVE FROM',
            'LEAVE TO',
            'REASON',
            'STATUS'
        ]);

        // ✅ Data
        foreach ($data as $d) {
            fputcsv($handle, [
                $d->id,
                $d->idno,
                $d->employee,
                $d->type,
                $d->leavefrom,
                $d->leaveto,
                $d->reason,
                $d->status
            ]);
        }

        fclose($handle);

    }, $fileName);
}

public function birthdaysReport() 
{
    if (permission::permitted('reports') == 'fail') {
        return redirect()->route('denied');
    }

    $data = table::people()
        ->join('tbl_company_data', 'tbl_people.id', '=', 'tbl_company_data.reference')
        ->get();

    if ($data->isEmpty()) {
        return redirect()->back()->with('error', 'No birthday records found.');
    }

    $date = date('Y-m-d');
    $time = date('His');
    $fileName = "employee-birthdays-{$date}T{$time}.csv";

    return response()->streamDownload(function () use ($data) {

        $handle = fopen('php://output', 'w');

        // ✅ Header
        fputcsv($handle, [
            'ID',
            'EMPLOYEE NAME',
            'DEPARTMENT',
            'POSITION',
            'BIRTHDAY',
            'MOBILE NUMBER'
        ]);

        // ✅ Data
        foreach ($data as $d) {
            fputcsv($handle, [
                $d->idno,
                trim($d->lastname . ' ' . $d->firstname . ' ' . $d->mi),
                $d->department,
                $d->jobposition,
                $d->birthday,
                $d->mobileno
            ]);
        }

        fclose($handle);

    }, $fileName);
}
public function accountReport() 
{
    if (permission::permitted('reports') == 'fail') {
        return redirect()->route('denied');
    }

    $users = table::users()->get();

    if ($users->isEmpty()) {
        return redirect()->back()->with('error', 'No accounts found.');
    }

    $date = date('Y-m-d');
    $time = date('His');
    $fileName = "employee-accounts-{$date}T{$time}.csv";

    return response()->streamDownload(function () use ($users) {

        $handle = fopen('php://output', 'w');

        // ✅ Header (ONLY ONCE)
        fputcsv($handle, [
            'EMPLOYEE NAME',
            'EMAIL',
            'ACCOUNT TYPE'
        ]);

        foreach ($users as $a) {

            $type = ($a->acc_type == 2) ? 'Admin' : 'Employee';

            fputcsv($handle, [
                $a->name,
                $a->email,
                $type
            ]);
        }

        fclose($handle);

    }, $fileName);
}

public function scheduleReport(Request $request) 
{
    if (permission::permitted('reports') == 'fail') {
        return redirect()->route('denied');
    }

    $id = $request->emp_id;

    $query = table::schedules();

    if ($id) {
        $query->where('idno', $id);
    }

    $data = $query->get();

    if ($data->isEmpty()) {
        return redirect('reports/employee-schedule')
            ->with('error', 'No schedule found.');
    }

    $date = date('Y-m-d');
    $time = date('His');
    $fileName = "schedule-reports-{$date}T{$time}.csv";

    return response()->streamDownload(function () use ($data) {

        $handle = fopen('php://output', 'w');

        // ✅ Header
        fputcsv($handle, [
            'IDNO',
            'EMPLOYEE',
            'START TIME',
            'OFF TIME',
            'DATE FROM',
            'DATE TO',
            'HOURS',
            'REST DAY',
            'STATUS'
        ]);

        // ✅ Data
        foreach ($data as $d) {

            fputcsv($handle, [
                $d->idno,
                $d->employee,
                $d->intime,
                $d->outime,
                $d->datefrom,
                $d->dateto,
                $d->hours,
                $d->restday,
                $d->archive
            ]);
        }

        fclose($handle);

    }, $fileName);
}

}
