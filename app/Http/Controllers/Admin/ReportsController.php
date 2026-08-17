<?php

namespace App\Http\Controllers\Admin;
use DB;
use PDF;
use DateTimeZone;
use DateTime;
use App\Classes\table;
use App\Classes\permission;
use App\Http\Requests;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ReportsController extends Controller
{
	public function index() 
	{
		if (permission::permitted('reports')=='fail'){ return redirect()->route('denied'); }
		$lastviews = table::reportviews()->get();

    	return view('admin.reports', ['lastviews' => $lastviews]);
    }

	public function empList(Request $request) 
	{
		if (permission::permitted('reports')=='fail'){ return redirect()->route('denied'); }
		
		$today = date('M, d Y');
		$empList = table::people()->get();
		table::reportviews()->where('report_id', 1)->update(['last_viewed' => $today]);

		return view('admin.reports.report-employee-list', compact('empList'));
	}

	public function empAtten(Request $request) 
	{
		if (permission::permitted('reports')=='fail'){ return redirect()->route('denied'); }
		
		$today = date('M, d Y');
		$empAtten = table::attendance()->get();
		$employee = table::people()->join('tbl_company_data', 'tbl_people.id', '=', 'tbl_company_data.reference')->where('tbl_people.employmentstatus', 'Active')->get();
		table::reportviews()->where('report_id', 2)->update(array('last_viewed' => $today));

		return view('admin.reports.report-employee-attendance', compact('empAtten', 'employee'));
	}

	public function empLeaves(Request $request) 
	{
		if (permission::permitted('reports')=='fail'){ return redirect()->route('denied'); }
		
		$today = date('M, d Y');
		$employee = table::people()->join('tbl_company_data', 'tbl_people.id', '=', 'tbl_company_data.reference')->where('tbl_people.employmentstatus', 'Active')->get();
		$empLeaves = table::leaves()->get();
		table::reportviews()->where('report_id', 3)->update(array('last_viewed' => $today));

		return view('admin.reports.report-employee-leaves', compact('empLeaves', 'employee'));
	}

	public function empSched(Request $request) 
	{
		if (permission::permitted('reports')=='fail'){ return redirect()->route('denied'); }
		
		$today = date('M, d Y');
		$empSched = table::schedules()->orderBy('archive', 'ASC')->get();
		$employee = table::people()->join('tbl_company_data', 'tbl_people.id', '=', 'tbl_company_data.reference')->where('tbl_people.employmentstatus', 'Active')->get();
		table::reportviews()->where('report_id', 4)->update(array('last_viewed' => $today));
		$tf = table::settings()->value("time_format");

		return view('admin.reports.report-employee-schedule', compact('empSched', 'employee', 'tf'));
	}

	public function orgProfile(Request $request) 
	{
		if (permission::permitted('reports')=='fail'){ return redirect()->route('denied'); }
		
		$today = date('M, d Y');
		$ed = table::people()->join('tbl_company_data', 'tbl_people.id', '=', 'tbl_company_data.reference')->where('tbl_people.employmentstatus', 'Active')->get();
		
		$age_18_24 = table::people()->where([['age', '>=', '18'], ['age', '<=', '24']])->count();
		$age_25_31 = table::people()->where([['age', '>=', '25'], ['age', '<=', '31']])->count();
		$age_32_38 = table::people()->where([['age', '>=', '32'], ['age', '<=', '38']])->count();
		$age_39_45 = table::people()->where([['age', '>=', '39'], ['age', '<=', '45']])->count();
		$age_46_100 = table::people()->where('age', '>=', '46')->count();
		
		if($age_18_24 == null) {$age_18_24 = 0;};
		if($age_25_31 == null) {$age_25_31 = 0;};
		if($age_32_38 == null) {$age_32_38 = 0;};
		if($age_39_45 == null) {$age_39_45 = 0;};
		if($age_46_100 == null) {$age_46_100 = 0;};	

		$age_group = $age_18_24.','.$age_25_31.','.$age_32_38.','.$age_39_45.','.$age_46_100;
		$dcc = null; 
		$dpc = null;
		$dgc = null;
		$csc = null;
		$yhc = null;

		foreach ($ed as $c) { $comp[] = $c->company; $dcc = array_count_values($comp); }
		$cc = ($dcc == null) ? null : implode(', ', $dcc) . ',' ;

		foreach ($ed as $d) { $dept[] = $d->department; $dpc = array_count_values($dept); }
		$dc = ($dpc == null) ? null : implode(', ', $dpc) . ',' ;

		foreach ($ed as $g) { $gender[] = $g->gender; $dgc = array_count_values($gender); }
		$gc = ($dgc == null) ? null : implode(', ', $dgc) . ',' ;

		foreach ($ed as $cs) { $civilstatus[] = $cs->civilstatus; $csc = array_count_values($civilstatus); }
		$cg = ($csc == null) ? null : implode(', ', $csc) . ',' ;

		foreach ($ed as $yearhired) {
			$year[] = date("Y", strtotime($yearhired->startdate));
			asort($year); 
			$yhc = array_count_values($year);
		}
		$yc = ($yhc == null) ? null : implode(', ', $yhc) . ',' ;
		
		$orgProfile = table::companydata()->get();
		table::reportviews()->where('report_id', 5)->update(array('last_viewed' => $today));

		return view('admin.reports.report-organization-profile', compact('orgProfile', 'age_group', 'gc', 'dgc', 'cg', 'csc', 'yc', 'yhc', 'dc', 'dpc', 'dcc', 'cc'));
	}

	public function empBday(Request $request) 
	{
		if (permission::permitted('reports')=='fail'){ return redirect()->route('denied'); }
		
		$today = date('M, d Y');
		$empBday = table::people()->join('tbl_company_data', 'tbl_people.id', '=', 'tbl_company_data.reference')->get();
		table::reportviews()->where('report_id', 7)->update(['last_viewed' => $today]);

		return view('admin.reports.report-employee-birthdays', compact('empBday'));
	}

	public function userAccs(Request $request) 
	{
		if (permission::permitted('reports')=='fail'){ return redirect()->route('denied'); }
		
		$today = date('M, d Y');
		$userAccs = table::users()->get();
		table::reportviews()->where('report_id', 6)->update(['last_viewed' => $today]);

		return view('admin.reports.report-user-accounts', compact('userAccs'));
	}

	/**
	 * Build the enriched attendance dataset for one filter set:
	 *  - attendance rows (idno, date, employee, timein, timeout)
	 *  - each row's timein/timeout reformatted to a plain 24-hour
	 *    "HH:MM" string ("17:00", never "5:00 PM") - consistent
	 *    everywhere this report appears (page, AJAX refresh, PDF)
	 *  - each row's hourly pay rate, looked up via tbl_company_data -> tbl_people
	 *  - when filtered to a single employee (idno given), that employee's
	 *    full details (department, job title, company) attached to every
	 *    row so the front end can render a "Selected Employee" panel
	 *    without a second request
	 *
	 * FIX: date filtering used to require BOTH $datefrom and $dateto
	 * (whereBetween needs both ends), so picking only a start date or
	 * only an end date silently applied no date filter at all - the
	 * table would quietly fall back to showing every date. Now a single
	 * bound is honoured on its own (>= / <=), and both together still
	 * use whereBetween.
	 */
	private function buildAttendanceReport(?string $id, ?string $datefrom, ?string $dateto, ?string $employeeName = null)
	{
		$query = table::attendance()->select('idno', 'reference', 'date', 'employee', 'timein', 'timeout');

		// Resolve the selected employee's full profile FIRST (before
		// filtering attendance rows), so we have their canonical name and
		// numeric `reference` (tbl_people.id) available as fallback match
		// keys even if idno linkage is broken.
		$selectedEmployee = null;
		$personReference = null;
		if ($id) {
			$companyRow = DB::table('tbl_company_data')->where('idno', $id)->first();
			if ($companyRow && $companyRow->reference) {
				$personReference = $companyRow->reference; // tbl_people.id
				$personRow = DB::table('tbl_people')->where('id', $companyRow->reference)->first();
				if ($personRow) {
					$selectedEmployee = [
						'name' => trim($personRow->firstname . ' ' . $personRow->lastname),
						'idno' => $id,
						'department' => $companyRow->department,
						'jobposition' => $companyRow->jobposition,
						'company' => $companyRow->company,
						'perhourpay' => $personRow->perhourpay,
					];
				}
			}
		}

		// FIX: tbl_people_attendance.idno is varchar(11) while
		// tbl_company_data.idno (the source of the id the front end sends)
		// is varchar(255) - an exact `where('idno', $id)` silently matched
		// nothing whenever the two didn't line up byte-for-byte, and there
		// was no way to confirm from this environment whether that's
		// whitespace, truncation, or the two tables simply not sharing a
		// reliable idno value at all for every employee.
		//
		// tbl_people_attendance also has its own `reference` column - a
		// plain int(11) FK, the exact same shape as tbl_company_data's
		// `reference`, both pointing at tbl_people.id. That's a much more
		// reliable match key than a varchar idno (no formatting/length
		// issues possible on an integer), so it's tried first.
		//
		// The filter now matches a row if ANY of these line up:
		//   1. reference = tbl_people.id (numeric, most reliable)
		//   2. (trimmed, length-capped) idno matches
		//   3. the attendance row's plain-text `employee` name matches
		//      the selected employee's resolved name (guaranteed
		//      consistent since it's what's shown in the Employee Name
		//      column already)
		$matchName = $employeeName ?: ($selectedEmployee['name'] ?? null);

		if ($id || $personReference || $matchName) {
			$query->where(function ($q) use ($id, $personReference, $matchName) {
				if ($personReference) {
					$q->orWhere('reference', $personReference);
				}
				if ($id) {
					$normalizedId = substr(trim($id), 0, 11);
					$q->orWhereRaw('TRIM(idno) = ?', [$normalizedId]);
				}
				if ($matchName) {
					$q->orWhereRaw('TRIM(employee) = ?', [trim($matchName)]);
				}
			});
		}

		if ($datefrom && $dateto) {
			$query->whereBetween('date', [$datefrom, $dateto]);
		} elseif ($datefrom) {
			$query->where('date', '>=', $datefrom);
		} elseif ($dateto) {
			$query->where('date', '<=', $dateto);
		}

		$data = $query->orderBy('date')->get();

		$totalSeconds = 0;

		$data = $data->map(function ($item) use (&$totalSeconds, $selectedEmployee) {
			$rawIn = $item->timein;
			$rawOut = $item->timeout;

			$item->timein_display = $rawIn ? date('H:i', strtotime($rawIn)) : null;
			$item->timeout_display = $rawOut ? date('H:i', strtotime($rawOut)) : null;

			$item->hours = null;
			if ($rawIn && $rawOut) {
				$in = strtotime($rawIn);
				$out = strtotime($rawOut);
				if ($out < $in) {
					$out += 86400; // overnight shift
				}
				$item->hours = round(($out - $in) / 3600, 2);
				$totalSeconds += ($out - $in);
			}

			// Per-row pay rate - kept for backward compatibility with
			// anything reading item.pay directly. If a single employee is
			// selected, every row returned by the query above already
			// belongs to them (matched via reference/idno/name), so their
			// resolved pay rate applies directly - re-checking idno
			// equality here would reintroduce the same unreliable-idno
			// mismatch the query filter was just fixed to avoid.
			if ($selectedEmployee) {
				$item->pay = $selectedEmployee['perhourpay'];
			} else {
				$company = DB::table('tbl_company_data')->where('idno', $item->idno)->first();
				$item->pay = ($company && $company->reference)
					? optional(DB::table('tbl_people')->where('id', $company->reference)->first())->perhourpay
					: 0;
			}

			return $item;
		});

		return [
			'data' => $data,
			'totalHours' => round($totalSeconds / 3600, 2),
			'selectedEmployee' => $selectedEmployee,
		];
	}

	public function getEmpAtten(Request $request) 
	{
		if (permission::permitted('reports')=='fail'){ return redirect()->route('denied'); }
		
		$id = $request->id ?: null;
		$datefrom = $request->datefrom ?: null;
		$dateto = $request->dateto ?: null;
		$employeeName = $request->name ?: null;

		$report = $this->buildAttendanceReport($id, $datefrom, $dateto, $employeeName);

		return response()->json([
			'rows' => $report['data'],
			'totalHours' => $report['totalHours'],
			'selectedEmployee' => $report['selectedEmployee'],
		]);
	}

	/**
	 * Shared filter logic for the Leaves report, used by both the AJAX
	 * endpoint and the PDF export so they can never diverge.
	 *
	 * FIX (mirrors the Attendance report fix): matching on idno alone is
	 * unreliable if that column's format/length doesn't line up
	 * consistently across tables, so this also accepts the employee's
	 * name as a fallback match key. It also replaces the old four-way
	 * branching - which only handled "both dates or neither" and
	 * silently ignored a lone datefrom/dateto - with straightforward
	 * >=/<=/whereBetween handling that covers every combination.
	 */
	private function buildLeavesQuery(?string $id, ?string $datefrom, ?string $dateto, ?string $employeeName = null)
	{
		$query = table::leaves()->select('idno', 'employee', 'type', 'leavefrom', 'leaveto', 'status', 'reason');

		if ($id || $employeeName) {
			$query->where(function ($q) use ($id, $employeeName) {
				if ($id) {
					$q->orWhereRaw('TRIM(idno) = ?', [trim($id)]);
				}
				if ($employeeName) {
					$q->orWhereRaw('TRIM(employee) = ?', [trim($employeeName)]);
				}
			});
		}

		if ($datefrom && $dateto) {
			$query->whereBetween('leavefrom', [$datefrom, $dateto]);
		} elseif ($datefrom) {
			$query->where('leavefrom', '>=', $datefrom);
		} elseif ($dateto) {
			$query->where('leavefrom', '<=', $dateto);
		}

		return $query;
	}

	public function getEmpLeav(Request $request) 
	{
		if (permission::permitted('reports')=='fail'){ return redirect()->route('denied'); }

		$id = $request->id ?: null;
		$datefrom = $request->datefrom ?: null;
		$dateto = $request->dateto ?: null;
		$employeeName = $request->name ?: null;

		$data = $this->buildLeavesQuery($id, $datefrom, $dateto, $employeeName)->get();

		return response()->json($data);
	}

	public function getEmpSched(Request $request) 
	{
		if (permission::permitted('reports')=='fail'){ return redirect()->route('denied'); }
		
		$id = $request->id;
		
		if ($id == null) 
		{
			$data = table::schedules()->select('reference', 'employee', 'intime', 'outime', 'datefrom', 'dateto', 'hours', 'restday', 'archive')->orderBy('archive', 'ASC')->get();
			return response()->json($data);
		}

		if($id !== null) 
		{
		 	$data = table::schedules()->where('idno', $id)->select('reference', 'employee', 'intime', 'outime', 'datefrom', 'dateto', 'hours', 'restday', 'archive')->orderBy('archive', 'ASC')->get();
			return response()->json($data);
		} 
	}

	/* ================================================================
	   PDF EXPORTS
	   Each PDF method mirrors the exact filter logic of its matching
	   getEmp*() AJAX endpoint above, so the PDF a person downloads
	   always matches what's currently filtered on screen - never a
	   silently different dataset.
	   ================================================================ */

	public function attendancePdf(Request $request)
	{
		if (permission::permitted('reports')=='fail'){ return redirect()->route('denied'); }

		$id = $request->query('id');
		$datefrom = $request->query('datefrom');
		$dateto = $request->query('dateto');
		$employeeName = $request->query('name');

		$report = $this->buildAttendanceReport($id, $datefrom, $dateto, $employeeName);

		$data = $report['data'];
		$totalHours = $report['totalHours'];
		$selectedEmployee = $report['selectedEmployee'];

		$ratePerHour = $selectedEmployee['perhourpay'] ?? null;
		$totalPay = $ratePerHour !== null ? round($totalHours * $ratePerHour, 2) : null;

		$pdf = PDF::loadView('admin.reports.pdf.attendance-pdf', compact(
			'data', 'totalHours', 'ratePerHour', 'totalPay', 'selectedEmployee', 'datefrom', 'dateto'
		));
		$pdf->setPaper('a4', 'portrait');

		return $pdf->stream('attendance-report.pdf');
	}

	public function leavesPdf(Request $request)
	{
		if (permission::permitted('reports')=='fail'){ return redirect()->route('denied'); }

		$id = $request->query('id');
		$datefrom = $request->query('datefrom');
		$dateto = $request->query('dateto');
		$employeeName = $request->query('name');

		$data = $this->buildLeavesQuery($id, $datefrom, $dateto, $employeeName)->orderBy('leavefrom')->get();

		$pdf = PDF::loadView('admin.reports.pdf.leaves-pdf', compact('data', 'datefrom', 'dateto', 'employeeName'));
		$pdf->setPaper('a4', 'portrait');

		return $pdf->stream('leaves-report.pdf');
	}

	public function schedulePdf(Request $request)
	{
		if (permission::permitted('reports')=='fail'){ return redirect()->route('denied'); }

		$id = $request->query('id');

		$query = table::schedules()
			->select('reference', 'idno', 'employee', 'intime', 'outime', 'datefrom', 'dateto', 'hours', 'restday', 'archive');

		if ($id) {
			$query->where('idno', $id);
		}

		$data = $query->orderBy('archive')->get();
		$tf = table::settings()->value("time_format");
		$employeeName = $id ? optional($data->first())->employee : null;

		$pdf = PDF::loadView('admin.reports.pdf.schedule-pdf', compact('data', 'tf', 'employeeName'));
		$pdf->setPaper('a4', 'landscape');

		return $pdf->stream('schedule-report.pdf');
	}

	public function employeeListPdf()
	{
		if (permission::permitted('reports')=='fail'){ return redirect()->route('denied'); }

		$empList = table::people()->get();

		$pdf = PDF::loadView('admin.reports.pdf.employee-list-pdf', compact('empList'));
		$pdf->setPaper('a4', 'landscape');

		return $pdf->stream('employee-list-report.pdf');
	}

	public function birthdaysPdf()
	{
		if (permission::permitted('reports')=='fail'){ return redirect()->route('denied'); }

		$empBday = table::people()->join('tbl_company_data', 'tbl_people.id', '=', 'tbl_company_data.reference')->get();

		$pdf = PDF::loadView('admin.reports.pdf.birthdays-pdf', compact('empBday'));
		$pdf->setPaper('a4', 'portrait');

		return $pdf->stream('birthdays-report.pdf');
	}

	public function userAccountsPdf()
	{
		if (permission::permitted('reports')=='fail'){ return redirect()->route('denied'); }

		$userAccs = table::users()->get();

		$pdf = PDF::loadView('admin.reports.pdf.user-accounts-pdf', compact('userAccs'));
		$pdf->setPaper('a4', 'portrait');

		return $pdf->stream('user-accounts-report.pdf');
	}
}