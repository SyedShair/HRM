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
        if (permission::permitted('employees') == 'fail') {
            return redirect()->route('denied');
        }

        $companies = table::company()->orderBy('company')->get();

        $companyId = $request->query('company_id');
        $companyId = ($companyId !== null && is_numeric($companyId))
            ? (int) $companyId
            : null;

        // No "All Companies" option - always scope to a single company.
        // Default to the first company in the list if none was requested.
        if (!$companyId && $companies->isNotEmpty()) {
            $companyId = $companies->first()->id;
        }

        $data = $this->getEmployeesForCompany($companyId);
        $counts = $this->buildSummaryCounts($data);

        $emp_typeR = $data->where('employmenttype', 'Regular')
            ->where('employmentstatus', 'Active')
            ->count();

        $emp_typeT = $data->where('employmenttype', 'Trainee')
            ->where('employmentstatus', 'Active')
            ->count();

        $emp_genderM = $data->where('gender', 'Male')->count();
        $emp_genderR = $data->where('gender', 'Female')->count();

        $emp_allActive = $data->where('employmentstatus', 'Active')->count();
        $emp_allArchive = $data->where('employmentstatus', 'Archive')->count();

        $emp_file = $data->count();

        $number1 = $emp_allActive > 0
            ? round(($emp_allArchive / $emp_allActive) * 100, 2)
            : 0;

        return view('admin.employees', array_merge(
            $counts,
            compact(
                'data',
                'emp_typeR',
                'emp_typeT',
                'emp_genderM',
                'emp_genderR',
                'emp_allActive',
                'emp_file',
                'emp_allArchive',
                'companies',
                'companyId',
                'number1'
            )
        ));
    }

    public function api()
    {
        if (permission::permitted('employees') == 'fail') {
            return response()->json([
                'message' => 'Forbidden.'
            ], 403);
        }

        /*
         * IMPORTANT:
         * Match employee records using idno instead of reference.
         *
         * Some existing tbl_company_data.reference values are incorrect,
         * but idno correctly identifies the employee in tbl_people.
         */
        $data = DB::table('tbl_people')
            ->join(
                'tbl_company_data',
                'tbl_people.idno',
                '=',
                'tbl_company_data.idno'
            )
            ->select(
                'tbl_company_data.*',
                'tbl_people.*'
            )
            ->get();

        return response()->json($data);
    }

    /**
     * AJAX endpoint for company filter + summary cards.
     */
    public function filterByCompany(Request $request)
    {
        if (permission::permitted('employees') == 'fail') {
            return response('', 403);
        }

        $companyId = $request->query('company_id');

        $companyId = ($companyId !== null && is_numeric($companyId))
            ? (int) $companyId
            : null;

        $data = $this->getEmployeesForCompany($companyId);

        $counts = $this->buildSummaryCounts($data);

        $rowsHtml = view(
            'admin.partials.employees-rows',
            compact('data')
        )->render();

        return response()->json(
            array_merge(
                $counts,
                ['rows' => $rowsHtml]
            )
        );
    }

    /**
     * Get employees for a company.
     *
     * IMPORTANT:
     * We use idno to connect tbl_people and tbl_company_data.
     *
     * This fixes old records where:
     *
     * tbl_company_data.reference != tbl_people.id
     *
     * but:
     *
     * tbl_company_data.idno = tbl_people.idno
     */
    private function getEmployeesForCompany($companyId)
    {
        $q = table::people()
            ->join(
                'tbl_company_data',
                'tbl_people.idno',
                '=',
                'tbl_company_data.idno'
            )
            ->select(
                'tbl_company_data.*',
                'tbl_people.*'
            );

        /*
         * Company filtering uses the real company_id.
         */
        if ($companyId) {
            $q->where(
                'tbl_company_data.company_id',
                $companyId
            );
        }

        return $q->get();
    }

    /**
     * Recompute summary cards.
     *
     * Total / Active / Expiring / Expired
     */
    private function buildSummaryCounts($data)
    {
        $total = $data->count();

        $active = $data
            ->where('employmentstatus', 'Active')
            ->count();

        $expired = $data->filter(function ($e) {
            return $e->visaend &&
                Carbon::parse($e->visaend)->isPast();
        })->count();

        $expiring = $data->filter(function ($e) {
            if (!$e->visaend) {
                return false;
            }

            $days = now()->diffInDays(
                $e->visaend,
                false
            );

            return $days > 0 && $days <= 90;
        })->count();

        return compact(
            'total',
            'active',
            'expiring',
            'expired'
        );
    }

    public function new()
    {
        if (permission::permitted('employees-add') == 'fail') {
            return redirect()->route('denied');
        }

        $employees = table::people()->get();
        $company = table::company()->get();
        $department = table::department()->get();
        $jobtitle = table::jobtitle()->get();
        $leavegroup = table::leavegroup()->get();

        return view(
            'admin.new-employee',
            compact(
                'company',
                'department',
                'jobtitle',
                'employees',
                'leavegroup'
            )
        );
    }

    /**
     * AJAX endpoint for departments by company.
     */
    public function departmentsByCompany(Request $request)
    {
        $companyId = $request->query('company_id');

        if (!$companyId || !is_numeric($companyId)) {
            return response()->json([]);
        }

        $company = table::company()
            ->where('id', $companyId)
            ->first();

        if (!$company) {
            return response()->json([]);
        }

        $departments = table::department()
            ->where('company_id', $company->id)
            ->orderBy('department')
            ->get([
                'id',
                'department'
            ]);

        return response()->json($departments);
    }

    public function add(Request $request)
    {
        if (permission::permitted('employees-add') == 'fail') {
            return redirect()->route('denied');
        }

        $v = $request->validate([
            'lastname' => 'required|alpha_dash_space|max:155',
            'firstname' => 'required|alpha_dash_space|max:155',
            'emailaddress' => 'required|email|max:155',
            'idno' => 'required|max:155',
            'employmentstatus' => 'required|alpha_dash_space|max:155',
            'company_id' => 'required|integer',

            'jobtitle_id' =>
                'nullable|integer|exists:tbl_form_jobtitle,id',

            'address_line' => 'nullable|array',
            'address_line.*' => 'nullable|string|max:500',

            'address_from' => 'nullable|array',
            'address_from.*' => 'nullable|date',

            'address_to' => 'nullable|array',
            'address_to.*' => 'nullable|date',

            'doc_reference' => 'nullable|array',
            'doc_reference.*' => 'nullable|string|max:255',

            'address_doc' => 'nullable|array',
            'address_doc.*' =>
                'nullable|file|mimes:jpeg,jpg,png,pdf|max:4096',

            'image' =>
                'nullable|image|mimes:jpeg,jpg,png|max:2048',

            'sharecodeexpiry' =>
                'nullable|required_with:sharecode|date',
        ]);

        if (!table::company()
            ->where('id', $request->company_id)
            ->exists()) {

            return redirect('employees-new')
                ->withInput()
                ->with(
                    'error',
                    trans('Selected company is invalid.')
                );
        }

        $companyRow = table::company()
            ->where('id', $request->company_id)
            ->first();

        $company = mb_strtoupper($companyRow->company);
        $companyId = $companyRow->id;

        $jobtitleId = null;

        if ($request->filled('jobtitle_id')) {
            $jobtitleRow = table::jobtitle()
                ->where('id', $request->jobtitle_id)
                ->first();

            $jobtitleId = $jobtitleRow
                ? $jobtitleRow->id
                : null;
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

        $birthday = date(
            "Y-m-d",
            strtotime($request->birthday)
        );

        $nationalid = mb_strtoupper($request->nationalid);

        $sharecode = $request->sharecode ?: null;

        $sharecodeexpiry = $sharecode
            ? $this->toNullableDate(
                $request->sharecodeexpiry
            )
            : null;

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

        $startdate = date(
            "Y-m-d",
            strtotime($request->startdate)
        );

        $dateregularized = $request->dateregularized
            ? date(
                "Y-m-d",
                strtotime($request->dateregularized)
            )
            : null;

        $is_idno_taken = table::companydata()
            ->where('idno', $idno)
            ->exists();

        if ($is_idno_taken == 1) {
            return redirect('employees-new')
                ->with(
                    'error',
                    trans(
                        "Whoops! the ID Number is already taken."
                    )
                );
        }

        // Store address documents.
        $addressDocPaths = [];

        try {
            foreach (
                $request->file('address_doc', [])
                as $i => $docFile
            ) {
                $addressDocPaths[$i] =
                    $this->storeAddressDocument($docFile);
            }
        } catch (\RuntimeException $e) {
            return redirect('employees-new')
                ->withInput()
                ->with(
                    'error',
                    trans($e->getMessage())
                );
        }

        $addressEntries = $this->buildAddressEntries(
            $request->input('address_line', []),
            $request->input('address_from', []),
            $request->input('address_to', []),
            $request->input('doc_reference', []),
            $addressDocPaths
        );

        try {
            $avatarPath = $this->storeAvatarImage(
                $request->file('image')
            );
        } catch (\RuntimeException $e) {
            return redirect('employees-new')
                ->withInput()
                ->with(
                    'error',
                    trans($e->getMessage())
                );
        }

        try {
            DB::transaction(function () use (
                $lastname,
                $firstname,
                $mi,
                $age,
                $gender,
                $emailaddress,
                $civilstatus,
                $height,
                $weight,
                $mobileno,
                $birthday,
                $birthplace,
                $nationalid,
                $sharecode,
                $sharecodeexpiry,
                $ni,
                $homeaddress,
                $employmenttype,
                $employmentstatus,
                $avatarPath,
                $company,
                $department,
                $jobposition,
                $companyemail,
                $leaveprivilege,
                $idno,
                $companyId,
                $jobtitleId,
                $startdate,
                $dateregularized,
                $addressEntries,
                $request,
                &$refId
            ) {

                /*
                 * Create employee first.
                 */
                $refId = table::people()->insertGetId([
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
                    'idissuedate' =>
                        $this->toNullableDate(
                            $request->idissuedate
                        ),
                    'idexpirydate' =>
                        $this->toNullableDate(
                            $request->idexpirydate
                        ),
                    'homeaddress' => $homeaddress,
                    'employmenttype' => $employmenttype,
                    'employmentstatus' => $employmentstatus,
                    'avatar' => $avatarPath,
                    'perhourpay' => $request->perhourpay,
                    'accountpay' => $request->accountpay,
                ]);

                /*
                 * Keep reference pointing to the actual
                 * tbl_people.id for all NEW records.
                 */
                table::companydata()->insert([
                    [
                        'reference' => $refId,
                        'company' => $company,
                        'company_id' => $companyId,
                        'jobtitle_id' => $jobtitleId,
                        'department' => $department,
                        'jobposition' => $jobposition,
                        'companyemail' => $companyemail,
                        'leaveprivilege' => $leaveprivilege,
                        'jobduties' => $request->jobduties,
                        'idno' => $idno,
                        'visaend' =>
                            $this->toNullableDate(
                                $request->visaend
                            ),
                        'visastart' =>
                            $this->toNullableDate(
                                $request->visastart
                            ),
                        'startdate' => $startdate,
                        'jobtype' => $request->jobtype,
                        'COSCertificateNo' =>
                            $request->COSCertificateNo,
                        'cosexpiry' =>
                            $this->toNullableDate(
                                $request->cosexpiry
                            ),
                        'visastatus' => $request->visastatus,
                        'kinno' => $request->kinno,
                        'kinname' => $request->kinname,
                        'workchecks' => $request->workchecks,
                        'dateregularized' => $dateregularized,
                    ],
                ]);

                /*
                 * Address history.
                 */
                if (!empty($addressEntries)) {
                    $addressRows = [];

                    foreach ($addressEntries as $entry) {
                        $addressRows[] = [
                            'reference' => $refId,
                            'address_line' => $entry['address'],
                            'date_from' =>
                                $entry['from']
                                    ? $entry['from']->format('Y-m-d')
                                    : null,
                            'date_to' =>
                                $entry['to']
                                    ? $entry['to']->format('Y-m-d')
                                    : null,
                            'is_current' =>
                                $entry['to'] === null,
                            'doc_reference' =>
                                $entry['doc_reference'],
                            'doc_file' =>
                                $entry['doc_file'],
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }

                    table::addresshistory()
                        ->insert($addressRows);
                }
            });
        } catch (\Exception $e) {

            if ($avatarPath) {
                Storage::disk('public')
                    ->delete($avatarPath);
            }

            foreach ($addressDocPaths as $path) {
                if ($path) {
                    Storage::disk('public')
                        ->delete($path);
                }
            }

            \Log::error(
                'Failed to add employee: ' .
                $e->getMessage()
            );

            return redirect('employees-new')
                ->withInput()
                ->with(
                    'error',
                    trans(
                        'Something went wrong while saving the employee. Please try again.'
                    )
                );
        }

        return redirect('employees')
            ->with(
                'success',
                trans("New employee has been added!")
            );
    }

    private function storeAvatarImage($file)
    {
        if (!$file) {
            return null;
        }

        if (
            @getimagesize(
                $file->getRealPath()
            ) === false
        ) {
            throw new \RuntimeException(
                'The uploaded file is not a valid image.'
            );
        }

        $extension = strtolower(
            $file->getClientOriginalExtension()
                ?: $file->extension()
        );

        $filename =
            Str::uuid()->toString() .
            '.' .
            $extension;

        return $file->storeAs(
            'avatars',
            $filename,
            'public'
        );
    }

    private function storeAddressDocument($file)
    {
        if (!$file) {
            return null;
        }

        $extension = strtolower(
            $file->getClientOriginalExtension()
                ?: $file->extension()
        );

        $filename =
            Str::uuid()->toString() .
            '.' .
            $extension;

        return $file->storeAs(
            'address-documents',
            $filename,
            'public'
        );
    }

    private function buildAddressEntries(
        array $addressLines,
        array $dateFrom,
        array $dateTo,
        array $docReferences = [],
        array $docFilePaths = []
    ) {
        $entries = [];

        foreach ($addressLines as $i => $line) {

            $line = trim((string) $line);

            if ($line === '') {
                continue;
            }

            $from = !empty($dateFrom[$i])
                ? Carbon::parse(
                    $dateFrom[$i]
                )->startOfDay()
                : null;

            $to = !empty($dateTo[$i])
                ? Carbon::parse(
                    $dateTo[$i]
                )->startOfDay()
                : null;

            $entries[] = [
                'address' =>
                    mb_strtoupper($line),

                'from' =>
                    $from,

                'to' =>
                    $to,

                'doc_reference' =>
                    isset($docReferences[$i]) &&
                    trim(
                        (string) $docReferences[$i]
                    ) !== ''
                        ? mb_strtoupper(
                            trim(
                                (string) $docReferences[$i]
                            )
                        )
                        : null,

                'doc_file' =>
                    $docFilePaths[$i] ?? null,
            ];
        }

        return $entries;
    }

    private function toNullableDate($value)
    {
        if (empty($value)) {
            return null;
        }

        $timestamp = strtotime($value);

        return $timestamp !== false
            ? date('Y-m-d', $timestamp)
            : null;
    }
}
