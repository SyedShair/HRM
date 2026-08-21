<?php

namespace App\Http\Controllers\Admin;
use DB;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Classes\table;
use App\Classes\permission;
use App\Http\Requests;
use Carbon\Carbon;
use Illuminate\Http\File;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Controller;

class ProfileController extends Controller
{

  public function view($id, Request $request)
{
    if (permission::permitted('employees-view')=='fail'){ return redirect()->route('denied'); }

    $p = table::people()->where('id', $id)->first();

    // Fail gracefully instead of letting the blade dereference a null
    // $p a few lines down and 500.
    if (!$p) {
        return redirect('employees')->with('error', trans('That employee record could not be found.'));
    }

    $c = table::companydata()->where('reference', $id)->first();
    $i = $p->avatar;
    $leavetype = table::leavetypes()->get();
    $leavegroup = table::leavegroup()->get();

    // Job Duties comes from tbl_form_jobtitle (the master record for
    // that position) via the real jobtitle_id FK on tbl_company_data,
    // rather than a free-text match.
    $rawjobtitle = ($c && $c->jobtitle_id) ? table::jobtitle()->where('id', $c->jobtitle_id)->first() : null;

    // Full 5-year address history for this employee, each row carrying
    // a ready-to-use doc_url so the profile page can actually display
    // (or link to) any supporting document/image that was uploaded
    // against it. Previously this page never queried
    // tbl_address_history at all, so uploaded address documents were
    // invisible here even though they were being saved correctly.
    $addressHistory = $this->addressHistoryQuery($id)->get()->map(fn ($row) => $this->withDocumentUrl($row));

    return view('admin.profile-view', compact('p', 'c', 'i', 'leavetype', 'leavegroup', 'rawjobtitle', 'addressHistory'));
}
   	public function delete($id, Request $request)
    {
		if (permission::permitted('employees-delete')=='fail'){ return redirect()->route('denied'); }

		return view('admin.delete-employee', compact('id'));
   	}

	public function clear(Request $request) 
	{
		if (permission::permitted('employees-delete')=='fail'){ return redirect()->route('denied'); }
		
		$id = $request->id;

		// Clean up any files this employee owns on disk before wiping
		// the DB rows, otherwise the avatar and every address-history
		// document become permanently orphaned storage.
		$avatar = table::people()->where('id', $id)->value('avatar');
		if ($avatar) {
			Storage::disk('public')->delete($avatar);
		}
		table::addresshistory()->where('reference', $id)->get()->each(function ($row) {
			if ($row->doc_file) {
				Storage::disk('public')->delete($row->doc_file);
			}
		});

		table::people()->where('id', $id)->delete();
		table::companydata()->where('reference', $id)->delete();
		table::attendance()->where('reference', $id)->delete();
		table::schedules()->where('reference', $id)->delete();
		table::leaves()->where('reference', $id)->delete();
		table::addresshistory()->where('reference', $id)->delete();
		table::users()->where('reference', $id)->delete();

		return redirect('employees')->with('success', trans("Employee information has been deleted!"));
	}

   	public function archive($id, Request $request)
    {
		if (permission::permitted('employees-archive')=='fail'){ return redirect()->route('denied'); }

		// The route already binds $id - it was previously being thrown
		// away and silently replaced with $request->id, which meant the
		// route parameter was dead code and archiving relied entirely on
		// a same-named hidden form field being present.
		$request->validate([
    'reason' => 'nullable|string|max:455',
]);

$person = table::people()->where('id', $id)->first();

if (!$person) {
    return redirect('employees')->with('error', 'Employee not found.');
}

if ($person->employmentstatus === 'Archived') {

    table::people()->where('id', $id)->update([
        'employmentstatus' => 'Active',
    ]);

    table::users()->where('reference', $id)->update([
        'status' => '1',
    ]);

    $message = 'Employee has been made Active!';

} else {

    table::people()->where('id', $id)->update([
        'employmentstatus' => 'Archived',
    ]);

    table::users()->where('reference', $id)->update([
        'status' => '0',
    ]);

    if ($request->filled('reason')) {
        table::companydata()->where('reference', $id)->update([
            'reason' => mb_strtoupper(trim($request->reason)),
        ]);
    }

    $message = 'Employee information has been archived!';
}

return redirect('employees')->with('success', $message);
   	}

	public function editPerson($id)
    {
		if (permission::permitted('employees-edit')=='fail'){ return redirect()->route('denied'); }

		// NOTE: match on the FK 'reference' column (the employee's id),
		// NOT tbl_company_data's own 'id' - same as view() above and
		// updatePerson() below. Matching on 'id' here was returning null
		// for any employee whose company_data row id != employee id,
		// which then crashed the blade the moment it touched
		// $company_details->company_id.
		$company_details = table::companydata()->where('reference', $id)->first();
		$person_details = table::people()->where('id', $id)->first();

		// If either core record is missing, fail gracefully instead of
		// letting the blade dereference a null property and 500.
		if (!$person_details || !$company_details) {
			return redirect('employees')->with('error', trans('That employee record could not be found.'));
		}

		$company = table::company()->get();
		$department = table::department()->get();
		$jobtitle = table::jobtitle()->get();
		$leavegroup = table::leavegroup()->get();
		$e_id = ($person_details->id == null) ? 0 : Crypt::encryptString($person_details->id) ;

		// Existing 5-year address history rows for this employee, oldest
		// first - the edit form pre-fills these instead of seeding a
		// single blank "current address" row like the New Employee form.
		// Each row also carries doc_url so the edit form can show a
		// thumbnail/link of whatever document is already on file,
		// instead of looking like nothing was ever uploaded.
		$addressHistory = $this->addressHistoryQuery($person_details->id)
			->get()
			->map(function ($row) {
				// <input type="date"> only accepts an exact "YYYY-MM-DD"
				// string. If the underlying column is datetime/timestamp
				// rather than date, the raw value (e.g. "2020-01-15
				// 00:00:00") gets silently rejected or mis-parsed by the
				// browser, which is what was showing the wrong dates.
				$row->date_from = $row->date_from ? Carbon::parse($row->date_from)->format('Y-m-d') : null;
				$row->date_to = $row->date_to ? Carbon::parse($row->date_to)->format('Y-m-d') : null;
				return $this->withDocumentUrl($row);
			});

        return view('admin.edits.edit-personal-info', compact('company_details', 'person_details', 'company', 'department', 'jobtitle', 'leavegroup', 'e_id', 'addressHistory'));
    }

    public function updatePerson(Request $request)
    {
		if (permission::permitted('employees-edit')=='fail'){ return redirect()->route('denied'); }

		$v = $request->validate([
			'id' => 'required|max:200',
			'lastname' => 'required|alpha_dash_space|max:155',
			'firstname' => 'required|alpha_dash_space|max:155',
			'emailaddress' => 'required|email|max:155',
			'idno' => 'required|max:155',
			'employmentstatus' => 'required|alpha_dash_space|max:155',

			// The Company field submits the company's id (see
			// edit-personal-info's Company dropdown), which has to be
			// resolved back to a name below since tbl_company_data.company
			// is a text column, not a foreign key.
			'company_id' => 'nullable|integer|exists:tbl_form_company,id',

			// Same pattern as company_id: the Job Title field submits
			// the master job-title's id, stored on the real
			// tbl_company_data.jobtitle_id FK. This was previously
			// collected on-screen (the dropdown is loaded in
			// editPerson()) but never actually saved on submit.
			'jobtitle_id' => 'nullable|integer|exists:tbl_form_jobtitle,id',

			// Optional free-text reason accompanying a status change
			// (e.g. resignation/termination), stored in
			// tbl_company_data.reason.
			'reason' => 'nullable|string|max:455',

			// Real image validation - checks actual file content via
			// finfo, not just the extension, and caps size at 2MB. Was
			// previously missing entirely on this form, unlike Add.
			'image' => 'nullable|image|mimes:jpeg,jpg,png|max:2048',

			// Address history arrays - same structural validation as Add.
			'address_line' => 'nullable|array',
			'address_line.*' => 'nullable|string|max:500',
			'address_from' => 'nullable|array',
			'address_from.*' => 'nullable|date',
			'address_to' => 'nullable|array',
			'address_to.*' => 'nullable|date',
			'doc_reference' => 'nullable|array',
			'doc_reference.*' => 'nullable|string|max:255',
			'address_doc' => 'nullable|array',
			'address_doc.*' => 'nullable|file|mimes:jpeg,jpg,png,pdf|max:4096',

			// Share code is optional. Its expiry is only required if a
			// share code was actually entered - never the other way round.
			'sharecodeexpiry' => 'nullable|required_with:sharecode|date',
		]);

		$id = Crypt::decryptString($request->id);
		$lastname = mb_strtoupper($request->lastname);
		$firstname = mb_strtoupper($request->firstname);
		$mi = mb_strtoupper($request->mi);
		$age = $request->age;
		$gender = mb_strtoupper($request->gender);
		$emailaddress =  mb_strtolower($request->emailaddress);
		$civilstatus = mb_strtoupper($request->civilstatus);
		$height = $request->height;
		$weight = $request->weight;
		$mobileno = $request->mobileno;
		$birthday = date("Y-m-d", strtotime($request->birthday));
		$nationalid = mb_strtoupper($request->nationalid);
		$birthplace = mb_strtoupper($request->birthplace);
		$homeaddress = mb_strtoupper($request->homeaddress);

		// Resolve the submitted company_id back to the company's name,
		// since tbl_company_data.company stores text, matching how
		// EmployeesController::add() saves it. If company_id wasn't
		// submitted for any reason, fall back to the employee's
		// currently-saved company/company_id instead of blanking it out.
		$existingCompanyData = table::companydata()->where('reference', $id)->first();

		$company = $existingCompanyData->company ?? null;
		$companyId = $existingCompanyData->company_id ?? null;

		if ($request->filled('company_id')) {
			$companyRow = table::company()->where('id', $request->company_id)->first();
			if ($companyRow) {
				$company = mb_strtoupper($companyRow->company);
				$companyId = $companyRow->id;
			}
		}

		// Same fallback pattern as company above: keep the existing
		// jobtitle_id unless a new one was actually submitted, so a form
		// post that omits the field never silently blanks it out.
		$jobtitleId = $existingCompanyData->jobtitle_id ?? null;
		if ($request->filled('jobtitle_id')) {
			$jobtitleRow = table::jobtitle()->where('id', $request->jobtitle_id)->first();
			if ($jobtitleRow) {
				$jobtitleId = $jobtitleRow->id;
			}
		}

		// Reason: only overwrite what's stored if one was actually
		// submitted on this request; never blank out a previously
		// recorded reason just because the field was left empty here.
		$reason = $request->filled('reason')
			? mb_strtoupper(trim($request->reason))
			: ($existingCompanyData->reason ?? null);

		// Share code is optional - only require/compute an expiry if a
		// code was actually entered. Never block the update when blank.
		$sharecode = $request->sharecode ?: null;
		$sharecodeexpiry = $sharecode ? $this->toNullableDate($request->sharecodeexpiry) : null;

		$department = mb_strtoupper($request->department);
		$jobposition = mb_strtoupper($request->jobposition);
		$companyemail = mb_strtolower($request->companyemail);
		$leaveprivilege = $request->leaveprivilege;
		$idno = mb_strtoupper($request->idno);
		$employmenttype = $request->employmenttype;
		$employmentstatus = $request->employmentstatus;

		$existingAvatar = table::people()->where('id', $id)->value('avatar');

		// ---- Avatar: same professional handling as EmployeesController::add() ----
		try {
			$newAvatarPath = $this->storeAvatarImage($request->file('image'));
		} catch (\RuntimeException $e) {
			return redirect('profile/edit/'.$id)->withInput()->with('error', trans($e->getMessage()));
		}

		$avatarToSave = $newAvatarPath ?: $existingAvatar;

		// ---- Store any per-address supporting documents ----
		$addressDocPaths = [];
		try {
			foreach ($request->file('address_doc', []) as $i => $docFile) {
				$addressDocPaths[$i] = $this->storeAddressDocument($docFile);
			}
		} catch (\RuntimeException $e) {
			if ($newAvatarPath) {
				Storage::disk('public')->delete($newAvatarPath);
			}
			return redirect('profile/edit/'.$id)->withInput()->with('error', trans($e->getMessage()));
		}

		try {
			DB::transaction(function () use (
				$id, $lastname, $firstname, $mi, $age, $gender, $emailaddress, $civilstatus,
				$height, $weight, $mobileno, $birthday, $nationalid, $birthplace, $homeaddress,
				$avatarToSave, $company, $companyId, $jobtitleId, $reason, $department, $jobposition, $sharecode,
				$sharecodeexpiry, $companyemail, $leaveprivilege, $idno, $employmenttype,
				$employmentstatus, $request, $addressDocPaths
			) {
				table::people()->where('id', $id)->update([
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
					'homeaddress' => $homeaddress,
					'employmenttype' => $employmenttype,
					'employmentstatus' => $employmentstatus,
					'avatar' => $avatarToSave,
					'perhourpay' => $request->perhourpay,
					'accountpay'  =>  $request->accountpay,
					'sharecode' => $sharecode,
					'sharecode_expires_at' => $sharecodeexpiry,
					'NI' => $request->ni,
					'idissuedate' => $this->toNullableDate($request->idissuedate),
					'idexpirydate' => $this->toNullableDate($request->idexpirydate),
				]);

				table::companydata()->where('reference', $id)->update([
					'company' => $company,
					'company_id' => $companyId,
					'jobtitle_id' => $jobtitleId,
					'department' => $department,
					'jobposition' => $jobposition,
					'companyemail' => $companyemail,
					'leaveprivilege' => $leaveprivilege,
					'idno' => $idno,
					'jobtype' => $request->jobtype,
					'COSCertificateNo' => $request->COSCertificateNo,
					'cosexpiry' => $this->toNullableDate($request->cosexpiry),
					'visaend' => $this->toNullableDate($request->visaend),
					'visastart' => $this->toNullableDate($request->visastart),
					'visastatus' => $request->visastatus,
					'kinno' => $request->kinno,
					'kinname' => $request->kinname,
					'workchecks' => $request->workchecks,
					'jobduties' => $request->jobduties,
					'startdate' => $this->toNullableDate($request->startdate),
					'dateregularized' => $this->toNullableDate($request->dateregularized),
					'reason' => $reason,
				]);

				$this->syncAddressHistory($id, $request, $addressDocPaths);
			});
		} catch (\Exception $e) {
			// Roll back any newly-uploaded files if the DB transaction failed.
			if ($newAvatarPath) {
				Storage::disk('public')->delete($newAvatarPath);
			}
			foreach ($addressDocPaths as $path) {
				if ($path) {
					Storage::disk('public')->delete($path);
				}
			}

			// Log the real reason instead of only showing a generic
			// message, so failures like this are diagnosable from
			// storage/logs next time.
			\Log::error('Failed to update employee #'.$id.': '.$e->getMessage());

			return redirect('profile/edit/'.$id)->withInput()->with('error', trans('Something went wrong while updating this employee. Please try again.'));
		}

		// Only now that the DB update succeeded is it safe to remove the
		// old avatar file - deleting it earlier and then failing the
		// transaction would leave the employee with no photo at all.
		if ($newAvatarPath && $existingAvatar && $existingAvatar !== $newAvatarPath) {
			Storage::disk('public')->delete($existingAvatar);
		}
		
    	return redirect('profile/edit/'.$id)->with('success', trans("Employee information has been updated!"));
   	}

	/**
	 * Sync the submitted address history rows against the DB for one
	 * employee: update rows that came with an existing address_id,
	 * insert new ones (rows added via "Add Another Address"), and
	 * delete any rows that were removed from the form.
	 *
	 * A row's existing doc_file is preserved if no new file was
	 * uploaded for that entry - file inputs can't be pre-filled by the
	 * browser, so "no file chosen" does not mean "remove the document".
	 *
	 * @param int $employeeId
	 * @param Request $request
	 * @param array $docPaths  Already-stored document paths, keyed by the
	 *                         same index as address_line - passed in so
	 *                         this can run inside the same DB transaction
	 *                         as the rest of the update without storing
	 *                         files twice.
	 */
	private function syncAddressHistory($employeeId, Request $request, array $docPaths = [])
	{
		$addressIds = $request->input('address_id', []);
		$addressLines = $request->input('address_line', []);
		$addressFrom = $request->input('address_from', []);
		$addressTo = $request->input('address_to', []);
		$docReferences = $request->input('doc_reference', []);

		$keptIds = [];

		foreach ($addressLines as $i => $line) {
			$line = trim((string) $line);
			if ($line === '') {
				continue;
			}

			$from = !empty($addressFrom[$i]) ? Carbon::parse($addressFrom[$i])->format('Y-m-d') : null;
			$to = !empty($addressTo[$i]) ? Carbon::parse($addressTo[$i])->format('Y-m-d') : null;

			$newDocPath = $docPaths[$i] ?? null;

			$rowId = $addressIds[$i] ?? null;

			$attributes = [
				'address_line' => mb_strtoupper($line),
				'date_from' => $from,
				'date_to' => $to,
				'is_current' => $to === null,
				'doc_reference' => isset($docReferences[$i]) && trim((string) $docReferences[$i]) !== ''
					? mb_strtoupper(trim((string) $docReferences[$i]))
					: null,
			];

			if ($rowId) {
				// Existing row - only touch doc_file if a new one was uploaded.
				if ($newDocPath) {
					$attributes['doc_file'] = $newDocPath;
				}
				$attributes['updated_at'] = now();

				table::addresshistory()
					->where('id', $rowId)
					->where('reference', $employeeId)
					->update($attributes);

				$keptIds[] = $rowId;
			} else {
				// New row - added via "Add Another Address" on this edit.
				$attributes['reference'] = $employeeId;
				$attributes['doc_file'] = $newDocPath;
				$attributes['created_at'] = now();
				$attributes['updated_at'] = now();

				$keptIds[] = table::addresshistory()->insertGetId($attributes);
			}
		}

		// Anything not resubmitted was removed via the "trash" icon - delete it,
		// cleaning up its stored document first.
		$toDelete = table::addresshistory()
			->where('reference', $employeeId)
			->when(!empty($keptIds), fn ($q) => $q->whereNotIn('id', $keptIds))
			->get();

		foreach ($toDelete as $row) {
			if ($row->doc_file) {
				Storage::disk('public')->delete($row->doc_file);
			}
		}

		table::addresshistory()
			->where('reference', $employeeId)
			->when(!empty($keptIds), fn ($q) => $q->whereNotIn('id', $keptIds))
			->delete();
	}

	/**
	 * Base query for one employee's address history, oldest first.
	 * Centralised so view(), editPerson(), and printProfile() can't
	 * drift out of sync with each other on ordering/scope.
	 *
	 * @param int $employeeId
	 * @return \Illuminate\Database\Query\Builder
	 */
	private function addressHistoryQuery($employeeId)
	{
		return table::addresshistory()
			->where('reference', $employeeId)
			->orderBy('date_from');
	}

	/**
	 * Annotate an address-history row with a browsable URL for its
	 * uploaded supporting document/image (doc_file), using the same
	 * "public" disk everything is stored on. Requires
	 * `php artisan storage:link` to have been run once, exactly like
	 * avatars already require.
	 *
	 * @param object $row
	 * @return object
	 */
	private function withDocumentUrl($row)
	{
		$row->doc_url = $row->doc_file ? Storage::disk('public')->url($row->doc_file) : null;
		return $row;
	}

	/**
	 * Stream a single address-history supporting document/image inline
	 * in the browser, so it can be opened directly (e.g. from a "View"
	 * link/thumbnail on the profile or edit page) without exposing the
	 * raw storage path or relying on the public disk being web-readable
	 * in every deployment.
	 *
	 * @param int $addressId
	 * @param Request $request
	 */
	public function viewAddressDocument($addressId, Request $request)
	{
		if (permission::permitted('employees-view') == 'fail') {
			return redirect()->route('denied');
		}

		$row = table::addresshistory()->where('id', $addressId)->first();

		if (!$row || !$row->doc_file || !Storage::disk('public')->exists($row->doc_file)) {
			abort(404, 'Document not found.');
		}

		return Storage::disk('public')->response($row->doc_file);
	}

	/**
	 * Store an uploaded avatar image professionally - identical approach
	 * to EmployeesController::storeAvatarImage(): confirms it's genuinely
	 * an image, generates a collision-proof UUID filename (never trusts
	 * the client-supplied name), stores via the Storage disk abstraction.
	 *
	 * @param \Illuminate\Http\UploadedFile|null $file
	 * @return string|null  Relative path, e.g. "avatars/uuid.jpg", or null if no file was uploaded
	 */
	private function storeAvatarImage($file)
	{
		if (!$file) {
			return null;
		}

		if (@getimagesize($file->getRealPath()) === false) {
			throw new \RuntimeException('The uploaded file is not a valid image.');
		}

		$extension = strtolower($file->getClientOriginalExtension() ?: $file->extension());
		$filename = Str::uuid()->toString() . '.' . $extension;

		return $file->storeAs('avatars', $filename, 'public');
	}

	/**
	 * Store a per-address supporting document - same collision-proof,
	 * disk-abstracted approach as EmployeesController::storeAddressDocument().
	 *
	 * @param \Illuminate\Http\UploadedFile|null $file
	 * @return string|null
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

	public function viewProfile(Request $request) 
	{
		$id = \Auth::user()->id;
		$myuser = table::users()->where('id', $id)->first();
		$myrole = table::roles()->where('id', $myuser->role_id)->value('role_name');

		return view('admin.update-profile', compact('myuser', 'myrole'));
	}

	public function viewPassword() 
	{
		return view('admin.update-password');
	}

	public function updateUser(Request $request) 
	{

		$v = $request->validate([
            'name' => 'required|max:100',
            'email' => 'required|email|max:100',
		]);
		
		$id = \Auth::id();
		$name = mb_strtoupper($request->name);
		$email = mb_strtolower($request->email);

		if($id == null) 
        {
            return redirect('personal/update-user')->with('error', trans("Whoops! Please fill the form completely."));
		}
		
		table::users()->where('id', $id)->update([
			'name' => $name,
			'email' => $email,
		]);

		return redirect('update-profile')->with('success', trans("Updated!"));
	}

	public function updatePassword(Request $request) 
	{

		$v = $request->validate([
            'currentpassword' => 'required|max:100',
            'newpassword' => 'required|min:8|max:100',
            'confirmpassword' => 'required|min:8|max:100',
		]);

		$id = \Auth::id();
		$p = \Auth::user()->password;
		$c_password = $request->currentpassword;
		$n_password = $request->newpassword;
		$c_p_password = $request->confirmpassword;

		if($id == null) 
        {
            return redirect('personal/update-user')->with('error', trans("Whoops! Please fill the form completely."));
		}

		if($n_password != $c_p_password) 
		{
			return redirect('update-password')->with('error', trans("New password does not match!"));
		}

		if(Hash::check($c_password, $p)) 
		{
			table::users()->where('id', $id)->update([
				'password' => Hash::make($n_password),
			]);

			return redirect('update-password')->with('success', trans("Updated!"));
		} else {
			return redirect('update-password')->with('error', trans("Oops! current password does not match."));
		}
	}


/**
 * Stream a full employee profile as a PDF, viewable inline in the
 * browser (not force-downloaded). Reuses the same data the profile
 * view page displays.
 */
public function printProfile($id)
{
    if (permission::permitted('employees') == 'fail') {
        return redirect()->route('denied');
    }

    $p = table::people()->where('id', $id)->first();

    if (!$p) {
        return redirect('employees')->with('error', trans('Employee not found.'));
    }

    $c = table::companydata()->where('reference', $id)->first();
    $i = $p->avatar;
    $leavetype = table::leavetypes()->get();
    $leavegroup = table::leavegroup()->get();
    $addressHistory = $this->addressHistoryQuery($id)->get()->map(fn ($row) => $this->withDocumentUrl($row));
    $rawjobtitle = ($c && $c->jobtitle_id) ? table::jobtitle()->where('id', $c->jobtitle_id)->first() : null;

    $pdf = Pdf::loadView('admin.reports.pdf.profile', compact('p', 'c', 'i', 'leavetype', 'leavegroup', 'addressHistory', 'rawjobtitle'))
        ->setPaper('a4', 'portrait');

    $filename = mb_strtoupper($p->firstname . '-' . $p->lastname . '-profile') . '.pdf';

    return $pdf->stream($filename);
}

/**
 * Human-readable countdown/overdue string with exact years, months,
 * and days — e.g. "1 year 3 months 12 days remaining" or
 * "Expired 2 months 5 days ago".
 */
private function expiryLabel(?string $date): array
{
    if (empty($date)) {
        return ['text' => '', 'class' => ''];
    }

    $expiry = \Carbon\Carbon::parse($date);
    $now = \Carbon\Carbon::now();

    if ($expiry->isPast()) {
        $diff = $now->diff($expiry);
        $parts = [];
        if ($diff->y > 0) $parts[] = $diff->y . ' year' . ($diff->y == 1 ? '' : 's');
        if ($diff->m > 0) $parts[] = $diff->m . ' month' . ($diff->m == 1 ? '' : 's');
        if ($diff->d > 0 || empty($parts)) $parts[] = $diff->d . ' day' . ($diff->d == 1 ? '' : 's');

        return ['text' => 'Expired ' . implode(' ', $parts) . ' ago', 'class' => 'bg-danger'];
    }

    $diff = $now->diff($expiry);
    $parts = [];
    if ($diff->y > 0) $parts[] = $diff->y . ' year' . ($diff->y == 1 ? '' : 's');
    if ($diff->m > 0) $parts[] = $diff->m . ' month' . ($diff->m == 1 ? '' : 's');
    if ($diff->d > 0 || empty($parts)) $parts[] = $diff->d . ' day' . ($diff->d == 1 ? '' : 's');

    $totalMonths = $now->diffInMonths($expiry);
    $class = $totalMonths <= 3 ? 'bg-warning' : 'bg-success';

    return ['text' => implode(' ', $parts) . ' remaining', 'class' => $class];
}
}