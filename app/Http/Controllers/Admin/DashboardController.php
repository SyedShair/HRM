<?php

namespace App\Http\Controllers\Admin;

use DB;
use App\Classes\table;
use App\Classes\permission;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        if (permission::permitted('dashboard') == 'fail') {
            return redirect()->route('denied');
        }

        // Companies live on tbl_form_company (no standalone companies table)
        $companies = DB::table('tbl_form_company')
            
            ->get();


        $defaultCompanyId = $companies->first()->id ?? null;

        $tf = table::settings()->value('time_format');

        return view('admin.dashboard', compact('companies', 'defaultCompanyId', 'tf'));
    }

    /**
     * AJAX endpoint: returns rendered partials for the selected company.
     */
    public function ajaxData(Request $request)
    {
        if (permission::permitted('dashboard') == 'fail') {
            return response()->json(['error' => 'denied'], 403);
        }

        $companyId = $request->input('company_id');

        if (!$companyId) {
            return response()->json(['error' => 'company_id is required'], 422);
        }

        $tf = table::settings()->value('time_format');
        $data = $this->buildDashboardData($companyId, $tf);

        return response()->json([
            'info_boxes'        => view('admin.dashboard.partials.info_boxes', $data)->render(),
            'newest_employees'  => view('admin.dashboard.partials.newest_employees', $data)->render(),
            'recent_attendance' => view('admin.dashboard.partials.recent_attendance', $data)->render(),
            'recent_leaves'     => view('admin.dashboard.partials.recent_leaves', $data)->render(),
            'birthday'          => view('admin.dashboard.partials.birthday', $data)->render(),
        ]);
    }

    /**
     * Shared query logic, scoped to a single company via tbl_company_data.
     */
    private function buildDashboardData($companyId, $tf)
    {
        $datenow = date('Y-m-d');

        // Everyone belonging to this company, per tbl_company_data
        $companyRows = table::companydata()
            ->where('company_id', $companyId)
            ->get(['reference', 'idno']);

        $peopleIds = $companyRows->pluck('reference')->filter()->unique()->values();
        $idnos     = $companyRows->pluck('idno')->filter()->unique()->values();

        // --- Attendance (online/offline today) ---
        $is_online = table::attendance()
            ->where('date', $datenow)
            ->whereIn('idno', $idnos)
            ->pluck('idno');
        $is_online_arr = json_decode(json_encode($is_online), true);
        $is_online_now = count($is_online);
        $is_offline_now = count(array_diff($idnos->all(), $is_online_arr));

        // --- Newest employees ---
        $emp_all_type = table::people()
            ->join('tbl_company_data', 'tbl_people.id', '=', 'tbl_company_data.reference')
            ->where('tbl_people.employmentstatus', 'Active')
            ->where('tbl_company_data.company_id', $companyId)
            ->orderBy('tbl_company_data.startdate', 'desc')
            ->take(6)
            ->select(
                'tbl_people.firstname',
                'tbl_people.lastname',
                'tbl_company_data.jobposition',
                'tbl_company_data.startdate',
                'tbl_company_data.visaend'
            )
            ->get();

        // --- Employee counts ---
        $emp_typeR = table::people()
            ->whereIn('id', $peopleIds)
            ->where('employmenttype', 'Regular')
            ->where('employmentstatus', 'Active')
            ->count();

        $emp_typeT = table::people()
            ->whereIn('id', $peopleIds)
            ->where('employmenttype', 'Part-Time')
            ->where('employmentstatus', 'Active')
            ->count();

        $emp_allActive = table::people()
            ->whereIn('id', $peopleIds)
            ->where('employmentstatus', 'Active')
            ->count();

        // --- Recent attendance ---
        $a = table::attendance()
            ->whereIn('idno', $idnos)
            ->latest('date')
            ->take(6)
            ->get();

        // --- Leaves ---
        $emp_approved_leave = table::leaves()
            ->where('status', 'Approved')
            ->whereIn('idno', $idnos)
            ->orderBy('leavefrom', 'desc')
            ->take(8)
            ->get();

        $emp_leaves_approve = table::leaves()
            ->where('status', 'Approved')
            ->whereIn('idno', $idnos)
            ->count();

        $emp_leaves_pending = table::leaves()
            ->where('status', 'Pending')
            ->whereIn('idno', $idnos)
            ->count();

        $emp_leaves_all = table::leaves()
            ->whereIn('idno', $idnos)
            ->whereIn('status', ['Approved', 'Pending'])
            ->count();

        // --- Birthdays (shown once per session, same as before) ---
        $today = Carbon::now()->format('m-d');

        $birthdays = DB::table('tbl_people')
            ->whereIn('id', $peopleIds)
            ->whereRaw("DATE_FORMAT(birthday, '%m-%d') = ?", [$today])
            ->get();

        if (!session()->has('birthday_shown')) {
            session()->put('birthday_shown', true);
        } else {
            $birthdays = collect();
        }

        return compact(
            'tf', 'emp_typeR', 'emp_typeT', 'emp_allActive',
            'emp_leaves_pending', 'emp_leaves_approve', 'emp_leaves_all',
            'emp_approved_leave', 'emp_all_type', 'a',
            'is_online_now', 'is_offline_now', 'birthdays'
        );
    }
}