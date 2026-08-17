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
		$c = table::companydata()->where('reference', $id)->first();
		$i = table::people()->select('avatar')->where('id', $id)->value('avatar');
		$leavetype = table::leavetypes()->get();
		$leavegroup = table::leavegroup()->get();

        return view('admin.profile-view', compact('p', 'c', 'i', 'leavetype', 'leavegroup'));
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
		table::people()->where('id', $id)->delete();
		table::companydata()->where('reference', $id)->delete();
		table::attendance()->where('reference', $id)->delete();
		table::schedules()->where('reference', $id)->delete();
		table::leaves()->where('reference', $id)->delete();
		table::users()->where('reference', $id)->delete();

		return redirect('employees')->with('success', trans("Employee information has been deleted!"));
	}

   	public function archive($id, Request $request)
    {
		if (permission::permitted('employees-archive')=='fail'){ return redirect()->route('denied'); }

		$id = $request->id;
		table::people()->where('id', $id)->update(['employmentstatus' => 'Archived']);
		table::users()->where('reference', $id)->update(['status' => '0']);

    	return redirect('employees')->with('success', trans("Employee information has been archived!"));
   	}

	public function editPerson($id)
    {
		if (permission::permitted('employees-edit')=='fail'){ return redirect()->route('denied'); }

		$company_details = table::companydata()->where('id', $id)->first();
		$person_details = table::people()->where('id', $id)->first();
		$company = table::company()->get();
		$department = table::department()->get();
		$jobtitle = table::jobtitle()->get();
		$leavegroup = table::leavegroup()->get();
		$e_id = ($person_details->id == null) ? 0 : Crypt::encryptString($person_details->id) ;

		// Existing 5-year address history rows for this employee, oldest
		// first - the edit form pre-fills these instead of seeding a
		// single blank "current address" row like the New Employee form.
		$addressHistory = table::addresshistory()
			->where('reference', $person_details->id)
			->orderBy('date_from')
			->get()
			->map(function ($row) {
				// <input type="date"> only accepts an exact "YYYY-MM-DD"
				// string. If the underlying column is datetime/timestamp
				// rather than date, the raw value (e.g. "2020-01-15
				// 00:00:00") gets silently rejected or mis-parsed by the
				// browser, which is what was showing the wrong dates.
				$row->date_from = $row->date_from ? Carbon::parse($row->date_from)->format('Y-m-d') : null;
				$row->date_to = $row->date_to ? Carbon::parse($row->date_to)->format('Y-m-d') : null;
				return $row;
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
		// EmployeesController::add() saves it. Previously this read
		// $request->company, which the form never actually submits -
		// every edit was silently blanking the employee's company.
		$company = null;
		if ($request->filled('company_id')) {
			$companyRow = table::company()->where('id', $request->company_id)->first();
			$company = $companyRow ? mb_strtoupper($companyRow->company) : null;
		}

		$department = mb_strtoupper($request->department);
		$jobposition = mb_strtoupper($request->jobposition);
		$companyemail = mb_strtolower($request->companyemail);
		$leaveprivilege = $request->leaveprivilege;
		$idno = mb_strtoupper($request->idno);
		$employmenttype = $request->employmenttype;
		$employmentstatus = $request->employmentstatus;

		$existingAvatar = table::people()->where('id', $id)->value('avatar');

		// ---- Avatar: same professional handling as EmployeesController::add() ----
		// Previously this used getClientOriginalName() + a raw move() to
		// public_path()/assets/faces - client-controlled filename, no
		// real image validation, and inconsistent with where Add stores
		// avatars (storage/app/public/avatars via the Storage disk).
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
				$avatarToSave, $company, $department, $jobposition, $companyemail, $leaveprivilege,
				$idno, $employmenttype, $employmentstatus,
				$request, $addressDocPaths
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
					'sharecode' => $request->sharecode,
					'NI' => $request->ni,
					'idissuedate' => $request->idissuedate,
					'idexpirydate' => $request->idexpirydate,
				]);

				table::companydata()->where('reference', $id)->update([
					'company' => $company,
					'department' => $department,
					'jobposition' => $jobposition,
					'companyemail' => $companyemail,
					'leaveprivilege' => $leaveprivilege,
					'idno' => $idno,
					'jobtype' => $request->jobtype,
					'COSCertificateNo' => $request->COSCertificateNo,
					'cosexpiry' => $request->cosexpiry,
					'visaend' => $request->visaend,
					'visastart' => $request->visastart,
					'visastatus' => $request->visastatus,
					'kinno' => $request->kinno,
					'kinname' => $request->kinname,
					'workchecks' => $request->workchecks,
					'jobduties' => $request->jobduties,
					'startdate' => $request->startdate ? date("Y-m-d", strtotime($request->startdate)) : null,
					'dateregularized' => $request->dateregularized ? date("Y-m-d", strtotime($request->dateregularized)) : null,
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
    $addressHistory = table::addresshistory()
        ->where('reference', $id)
        ->orderBy('date_from')
        ->get();

    $pdf = Pdf::loadView('admin.reports.pdf.profile', compact('p', 'c', 'i', 'leavetype', 'leavegroup', 'addressHistory'))
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