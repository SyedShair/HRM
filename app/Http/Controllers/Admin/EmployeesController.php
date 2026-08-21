<?php

namespace App\Http\Controllers\Admin;
use DB;
use Carbon\Carbon;
use App\Classes\table;
use App\Classes\permission;
use App\Http\Requests;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Controller;

class EmployeesController extends Controller
{

	public function index(Request $request)
	{
        if (permission::permitted('employees')=='fail'){ return redirect()->route('denied'); }

		$companies = table::company()->orderBy('company')->get();

		$companyId = $request->query('company_id');
		$companyId = ($companyId !== null && is_numeric($companyId)) ? (int) $companyId : null;

		// No "All Companies" option - always scope to a single company.
		// Default to the first company in the list if none was requested.
		if (!$companyId && $companies->isNotEmpty()) {
			$companyId = $companies->first()->id;
		}

		$data = $this->getEmployeesForCompany($companyId);
		$counts = $this->buildSummaryCounts($data);

		$emp_typeR = $data->where('employmenttype', 'Regular')
			->where('employmentstatus', 'Active')->count();

		$emp_typeT = $data->where('employmenttype', 'Trainee')
			->where('employmentstatus', 'Active')->count();

		$emp_genderM = $data->where('gender', 'Male')->count();
		$emp_genderR = $data->where('gender', 'Female')->count();
		$emp_allActive = $data->where('employmentstatus', 'Active')->count();
		$emp_allArchive = $data->where('employmentstatus', 'Archive')->count();

		$emp_file = $data->count();

		// Archive-to-active ratio, as a percentage. Guarded against
		// division by zero: the previous condition here
		// ($emp_allArchive != null OR $emp_allActive != null OR ...)
		// was true almost every time (loose "!= null" on an int is true
		// for anything but 0), so as soon as $emp_allActive was 0 with
		// at least one archived employee this line threw a
		// DivisionByZeroError on page load. It was also never actually
		// passed to the view (missing from the compact() below), so it
		// was dead code that could crash the page for nothing - now
		// fixed AND included in case the view wants it.
		$number1 = $emp_allActive > 0
			? round(($emp_allArchive / $emp_allActive) * 100, 2)
			: 0;

	    return view('admin.employees', array_merge($counts, compact(
	    	'data', 'emp_typeR', 'emp_typeT', 'emp_genderM', 'emp_genderR',
	    	'emp_allActive', 'emp_file', 'emp_allArchive', 'companies', 'companyId', 'number1'
	    )));
	}


	public function api()
	{
		// This endpoint was previously unauthenticated relative to every
		// other action in this controller - it dumped every employee's
		// full people+company_data record (including national ID,
		// share code, home address, etc.) to anyone who could hit the
		// route. Bring it in line with the same permission gate index()
		// uses.
		if (permission::permitted('employees') == 'fail') {
			return response()->json(['message' => 'Forbidden.'], 403);
		}

	    $data = DB::table('tbl_people')
	        ->join('tbl_company_data', 'tbl_people.id', '=', 'tbl_company_data.reference')
	        ->get();

	    return response()->json($data);
	}

	/**
	 * AJAX endpoint for the company filter dropdown + summary cards on
	 * the Employees page. Returns rendered table rows plus recalculated
	 * summary numbers (Total / Active / Expiring / Expired), all scoped
	 * to whichever company was selected.
	 */
	public function filterByCompany(Request $request)
	{
		if (permission::permitted('employees') == 'fail') {
			return response('', 403);
		}

		$companyId = $request->query('company_id');
		$companyId = ($companyId !== null && is_numeric($companyId)) ? (int) $companyId : null;

		$data = $this->getEmployeesForCompany($companyId);
		$counts = $this->buildSummaryCounts($data);

		$rowsHtml = view('admin.partials.employees-rows', compact('data'))->render();

		return response()->json(array_merge($counts, ['rows' => $rowsHtml]));
	}

	/**
	 * Shared query: people + company data, optionally scoped to one
	 * company. Matches on the real FK (tbl_company_data.company_id)
	 * first, falling back to the free-text company name for any rows
	 * that haven't been backfilled with a company_id yet.
	 *
	 * @param int|null $companyId
	 * @return \Illuminate\Support\Collection
	 */
	private function getEmployeesForCompany($companyId)
	{
		$q = table::people()
			->join('tbl_company_data', 'tbl_people.id', '=', 'tbl_company_data.reference')
			// Both tables have their own 'id' primary key. Without an
			// explicit select, MySQL returns both 'id' columns and the
			// LAST one wins when the row is hydrated - which was
			// tbl_company_data.id, not the employee's real id. That's
			// what sent Documents/Print PDF/QR (which use $employee->id)
			// to the wrong employee. Selecting tbl_company_data.* first
			// and tbl_people.* (id included) last guarantees
			// $employee->id is always the real tbl_people.id.
			->select('tbl_company_data.*', 'tbl_people.*');

		if ($companyId) {
			$companyRow = table::company()->where('id', $companyId)->first();
			$companyName = $companyRow ? mb_strtoupper(trim($companyRow->company)) : null;

			$q->where(function ($sub) use ($companyId, $companyName) {
				$sub->where('tbl_company_data.company_id', $companyId);

				if ($companyName) {
					$sub->orWhereRaw('UPPER(TRIM(tbl_company_data.company)) = ?', [$companyName]);
				}
			});
		}

		return $q->get();
	}

	/**
	 * Recompute the 4 summary cards (Total / Active / Expiring / Expired)
	 * from a given employee collection.
	 *
	 * @param \Illuminate\Support\Collection $data
	 * @return array
	 */
	private function buildSummaryCounts($data)
	{
		$total = $data->count();
		$active = $data->where('employmentstatus', 'Active')->count();

		$expired = $data->filter(function ($e) {
			return $e->visaend && Carbon::parse($e->visaend)->isPast();
		})->count();

		$expiring = $data->filter(function ($e) {
			if (!$e->visaend) return false;
			// now()->diffInDays($future, false) - now() is before the
			// expiry date, so this is positive, counting down to zero.
			// (The reverse order - visaend->diffInDays(now()) - is always
			// negative for a future date and made this card always read 0.)
			$days = now()->diffInDays($e->visaend, false);
			return $days > 0 && $days <= 90;
		})->count();

		return compact('total', 'active', 'expiring', 'expired');
	}

	public function new() 
	{
		if (permission::permitted('employees-add')=='fail'){ return redirect()->route('denied'); }
		
		$employees = table::people()->get();
		$company = table::company()->get();
		$department = table::department()->get();
		$jobtitle = table::jobtitle()->get();
		$leavegroup = table::leavegroup()->get();

	    return view('admin.new-employee', compact('company', 'department', 'jobtitle', 'employees', 'leavegroup'));
	}

	/**
	 * AJAX endpoint used by the "New Employee" form: given a company's id,
	 * return only the departments belonging to it (tbl_form_department
	 * filtered by company_id). Used by the Company -> Department cascade
	 * in new-employee.blade.php.
	 */
	public function departmentsByCompany(Request $request)
	{
		$companyId = $request->query('company_id');

		if (!$companyId || !is_numeric($companyId)) {
			return response()->json([]);
		}

		$company = table::company()->where('id', $companyId)->first();

		if (!$company) {
			return response()->json([]);
		}

		$departments = table::department()
			->where('company_id', $company->id)
			->orderBy('department')
			->get(['id', 'department']);

		return response()->json($departments);
	}
	
    public function add(Request $request)
    {

		if (permission::permitted('employees-add')=='fail'){ return redirect()->route('denied'); }
		
		$v = $request->validate([
			'lastname' => 'required|alpha_dash_space|max:155',
			'firstname' => 'required|alpha_dash_space|max:155',
			'emailaddress' => 'required|email|max:155',
			'idno' => 'required|max:155',
			'employmentstatus' => 'required|alpha_dash_space|max:155',
			'company_id' => 'required|integer',

			// Same pattern as company_id: the Job Title field submits
			// the master job-title's id, stored on the real
			// tbl_company_data.jobtitle_id FK, matching how
			// ProfileController resolves Job Duties for the profile
			// view/PDF. Previously never validated or saved on Add.
			'jobtitle_id' => 'nullable|integer|exists:tbl_form_jobtitle,id',

			// Address history arrays - structural validation only.
			// No continuity/coverage rule is enforced here - whatever
			// address rows are submitted are saved as-is.
			'address_line' => 'nullable|array',
			'address_line.*' => 'nullable|string|max:500',
			'address_from' => 'nullable|array',
			'address_from.*' => 'nullable|date',
			'address_to' => 'nullable|array',
			'address_to.*' => 'nullable|date',

			// Supporting document per address entry.
			'doc_reference' => 'nullable|array',
			'doc_reference.*' => 'nullable|string|max:255',
			'address_doc' => 'nullable|array',
			'address_doc.*' => 'nullable|file|mimes:jpeg,jpg,png,pdf|max:4096',

			// Real image validation - checks actual file content via
			// finfo, not just the extension, and caps size at 2MB.
			'image' => 'nullable|image|mimes:jpeg,jpg,png|max:2048',

			// Share code is optional. Its expiry is only required if a
			// share code was actually entered - never the other way round.
			'sharecodeexpiry' => 'nullable|required_with:sharecode|date',
		]);

		// The company dropdown posts the company's id. Resolve the
		// readable name here since tbl_company_data.company still
		// stores the name for reporting/exports.
		if (!table::company()->where('id', $request->company_id)->exists()) {
			return redirect('employees-new')->withInput()->with('error', trans('Selected company is invalid.'));
		}
		
		$companyRow = table::company()->where('id', $request->company_id)->first();
		$company = mb_strtoupper($companyRow->company);
		$companyId = $companyRow->id;

		// Resolve the submitted job title id the same defensive way as
		// company above - only trust it if it actually exists.
		$jobtitleId = null;
		if ($request->filled('jobtitle_id')) {
			$jobtitleRow = table::jobtitle()->where('id', $request->jobtitle_id)->first();
			$jobtitleId = $jobtitleRow ? $jobtitleRow->id : null;
		}
	  
		$lastname = mb_strtoupper($request->lastname);
		$firstname = mb_strtoupper($request->firstname);
		$mi = mb_strtoupper($request->mi);
		$age = $request->age;
		$gender = mb_strtoupper($request->gender);
		$emailaddress = mb_strtolower($request->emailaddress);
		$civilstatus = mb_strtoupper($request->civilstatus);
		$height = $request->height;
		$weight = $request->weight;
		$mobileno = $request->mobileno;
		$birthday = date("Y-m-d", strtotime($request->birthday));
		$nationalid = mb_strtoupper($request->nationalid);
		$sharecode = $request->sharecode ?: null;

		// Share code is optional - only compute an expiry if a code was
		// actually entered. Never block submission when it's blank.
		$sharecodeexpiry = $sharecode ? $this->toNullableDate($request->sharecodeexpiry) : null;

		$ni = $request->ni;
		$birthplace = mb_strtoupper($request->birthplace);
		$homeaddress = mb_strtoupper($request->homeaddress);
		$department = mb_strtoupper($request->department);
		$jobposition = mb_strtoupper($request->jobposition);
		
		$companyemail = mb_strtolower($request->companyemail);
		$leaveprivilege = $request->leaveprivilege;
		$idno = mb_strtoupper($request->idno);
		$employmenttype = $request->employmenttype;
		$employmentstatus = $request->employmentstatus;
		$startdate = date("Y-m-d", strtotime($request->startdate));
		$dateregularized = $request->dateregularized ? date("Y-m-d", strtotime($request->dateregularized)) : null;

		$is_idno_taken = table::companydata()->where('idno', $idno)->exists();

		if ($is_idno_taken == 1) 
		{
			return redirect('employees-new')->with('error', trans("Whoops! the ID Number is already taken."));
		}

		// ---- Store any per-address supporting documents first ----
		// Stored under their original array index so they can be matched
		// back up to the correct address line when building the rows below.
		$addressDocPaths = [];
		try {
			foreach ($request->file('address_doc', []) as $i => $docFile) {
				$addressDocPaths[$i] = $this->storeAddressDocument($docFile);
			}
		} catch (\RuntimeException $e) {
			return redirect('employees-new')->withInput()->with('error', trans($e->getMessage()));
		}

		// ---- Build address history rows ----
		// No continuity/gap/coverage validation - each submitted row is
		// taken as-is (blank lines skipped) and saved directly.
		$addressEntries = $this->buildAddressEntries(
			$request->input('address_line', []),
			$request->input('address_from', []),
			$request->input('address_to', []),
			$request->input('doc_reference', []),
			$addressDocPaths
		);

		try {
			$avatarPath = $this->storeAvatarImage($request->file('image'));
		} catch (\RuntimeException $e) {
			return redirect('employees-new')->withInput()->with('error', trans($e->getMessage()));
		}

		
		try {
			DB::transaction(function () use (
				$lastname, $firstname, $mi, $age, $gender, $emailaddress, $civilstatus,
				$height, $weight, $mobileno, $birthday, $birthplace, $nationalid,
				$sharecode, $sharecodeexpiry, $ni, $homeaddress, $employmenttype,
				$employmentstatus, $avatarPath, $company, $department, $jobposition,
				$companyemail, $leaveprivilege, $idno, $companyId, $jobtitleId, $startdate,
				$dateregularized, $addressEntries, $request, &$refId
			) {
				table::people()->insert([
					[
						'lastname' => $lastname,
						'firstname' => $firstname,
						'mi' => $mi,
						'age' => $age,
						'gender' => $gender,
						'emailaddress' => $emailaddress,
						'civilstatus' => $civilstatus,
						'height' => $height,
						'weight' => $weight,
						'mobileno' => $mobileno,
						'birthday' => $birthday,
						'birthplace' => $birthplace,
						'nationalid' => $nationalid,
						'sharecode' => $sharecode,
						'sharecode_expires_at' => $sharecodeexpiry,
						'NI' => $ni,
						'idissuedate' => $this->toNullableDate($request->idissuedate),
						'idexpirydate' => $this->toNullableDate($request->idexpirydate),
						'homeaddress' => $homeaddress,
						'employmenttype' => $employmenttype,
						'employmentstatus' => $employmentstatus,
						'avatar' => $avatarPath,
						'perhourpay' => $request->perhourpay,
						'accountpay'  =>  $request->accountpay
					],
				]);

				$refId = DB::getPdo()->lastInsertId();

				table::companydata()->insert([
					[
						'reference' => $refId,
						'company' => $company,
						'company_id' =>$companyId,
						'jobtitle_id' => $jobtitleId,
						'department' => $department,
						'jobposition' => $jobposition,
						'companyemail' => $companyemail,
						'leaveprivilege' => $leaveprivilege,
						'jobduties' => $request->jobduties,
						'idno' => $idno,
						'visaend'=> $this->toNullableDate($request->visaend),
						'visastart'=> $this->toNullableDate($request->visastart),
						'startdate' => $startdate,
						'jobtype' => $request->jobtype,
						'COSCertificateNo'=>$request->COSCertificateNo,
						'cosexpiry'=> $this->toNullableDate($request->cosexpiry),
						'visastatus'=>$request->visastatus,
						'kinno'=>$request->kinno,
						'kinname'=>$request->kinname,
						'workchecks'=>$request->workchecks,
						'dateregularized' => $dateregularized,
						// NOTE: 'jobduties' previously appeared twice in
						// this array (harmless but sloppy - the second
						// assignment silently won). Removed the duplicate.
					],
				]);

				// Persist whatever address history rows were submitted.
				if (!empty($addressEntries)) {
					$addressRows = [];
					foreach ($addressEntries as $entry) {
						$addressRows[] = [
							'reference'  => $refId,
							'address_line' => $entry['address'],
							'date_from'  => $entry['from'] ? $entry['from']->format('Y-m-d') : null,
							'date_to'    => $entry['to'] ? $entry['to']->format('Y-m-d') : null,
							'is_current' => $entry['to'] === null,
							'doc_reference' => $entry['doc_reference'],
							'doc_file'   => $entry['doc_file'],
							'created_at' => now(),
							'updated_at' => now(),
						];
					}
					table::addresshistory()->insert($addressRows);
				}
			});
		} catch (\Exception $e) {
			// Roll back any orphaned uploads if the DB transaction failed
			// after files were already written to disk.
			if ($avatarPath) {
				Storage::disk('public')->delete($avatarPath);
			}
			foreach ($addressDocPaths as $path) {
				if ($path) {
					Storage::disk('public')->delete($path);
				}
			}

			// Log the real reason instead of only showing a generic
			// message, so failures like this are diagnosable from
			// storage/logs next time.
			\Log::error('Failed to add employee: '.$e->getMessage());

			return redirect('employees-new')->withInput()->with('error', trans('Something went wrong while saving the employee. Please try again.'));
		}

    	return redirect('employees')->with('success', trans("New employee has been added!"));
    }

	/**
	 * Store an uploaded avatar image professionally:
	 *  - confirms the file is genuinely an image (not just a spoofed extension)
	 *  - generates a random, collision-proof filename (never trusts the
	 *    client-supplied name, which can contain path traversal characters)
	 *  - stores it via Laravel's Storage abstraction on the "public" disk,
	 *    keeping uploads out of source control and off the raw filesystem API
	 *
	 * Requires `php artisan storage:link` to have been run once, so files
	 * saved under storage/app/public/avatars are served at /storage/avatars/...
	 *
	 * @param \Illuminate\Http\UploadedFile|null $file
	 * @return string|null  Relative path stored in the DB, e.g. "avatars/uuid.jpg"
	 */
	private function storeAvatarImage($file)
	{
		if (!$file) {
			return null;
		}

		// Belt-and-braces on top of the 'image' validation rule: read the
		// actual file header rather than trusting the extension/MIME alone.
		if (@getimagesize($file->getRealPath()) === false) {
			throw new \RuntimeException('The uploaded file is not a valid image.');
		}

		$extension = strtolower($file->getClientOriginalExtension() ?: $file->extension());
		$filename = Str::uuid()->toString() . '.' . $extension;

		// storeAs() returns the relative path on the given disk, e.g.
		// "avatars/3f2a1e6b-9c2d-4e91-8b31-....jpg"
		return $file->storeAs('avatars', $filename, 'public');
	}

	/**
	 * Store a per-address supporting document (photo or scan of a utility
	 * bill, bank statement, etc.). Same collision-proof, disk-abstracted
	 * approach as storeAvatarImage(), but allows PDFs as well as images
	 * since these are often scanned statements.
	 *
	 * @param \Illuminate\Http\UploadedFile|null $file
	 * @return string|null  Relative path stored in the DB, e.g. "address-documents/uuid.pdf"
	 */
	private function storeAddressDocument($file)
	{
		if (!$file) {
			return null;
		}

		$extension = strtolower($file->getClientOriginalExtension() ?: $file->extension());
		$filename = Str::uuid()->toString() . '.' . $extension;

		return $file->storeAs('address-documents', $filename, 'public');
	}

	/**
	 * Turn the submitted address arrays into rows ready to insert.
	 * No continuity/gap/coverage checks - each non-blank line is kept
	 * as-is, in the order it was submitted.
	 *
	 * @param array $addressLines
	 * @param array $dateFrom
	 * @param array $dateTo
	 * @param array $docReferences  Same index as $addressLines - free-text doc ID/reference per entry
	 * @param array $docFilePaths   Same index as $addressLines - already-stored path of an uploaded proof document
	 * @return array
	 */
	private function buildAddressEntries(array $addressLines, array $dateFrom, array $dateTo, array $docReferences = [], array $docFilePaths = [])
	{
		$entries = [];

		foreach ($addressLines as $i => $line) {
			$line = trim((string) $line);
			if ($line === '') {
				continue;
			}

			$from = !empty($dateFrom[$i]) ? Carbon::parse($dateFrom[$i])->startOfDay() : null;
			$to = !empty($dateTo[$i]) ? Carbon::parse($dateTo[$i])->startOfDay() : null;

			$entries[] = [
				'address' => mb_strtoupper($line),
				'from'    => $from,
				'to'      => $to,
				'doc_reference' => isset($docReferences[$i]) && trim((string) $docReferences[$i]) !== ''
					? mb_strtoupper(trim((string) $docReferences[$i]))
					: null,
				'doc_file' => $docFilePaths[$i] ?? null,
			];
		}

		return $entries;
	}

	/**
	 * Convert a possibly-blank/invalid date string from the form into
	 * either a clean Y-m-d string or null - never an empty string, which
	 * MySQL rejects for DATE columns under strict mode.
	 *
	 * @param string|null $value
	 * @return string|null
	 */
	private function toNullableDate($value)
	{
		if (empty($value)) {
			return null;
		}

		$timestamp = strtotime($value);

		return $timestamp !== false ? date('Y-m-d', $timestamp) : null;
	}

}