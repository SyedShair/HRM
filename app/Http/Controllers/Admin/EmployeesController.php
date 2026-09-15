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

        // Avoid division by zero.
        $number1 = $emp_allActive > 0
            ? round(($emp_allArchive / $emp_allActive) * 100, 2)
            : 0;

        return view(
            'admin.employees',
            array_merge(
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
            )
        );
    }


    /**
     * API endpoint.
     */
    public function api()
    {
        if (permission::permitted('employees') == 'fail') {
            return response()->json([
                'message' => 'Forbidden.'
            ], 403);
        }

        /*
         * IMPORTANT:
         *
         * Both tbl_people and tbl_company_data contain an "id" column.
         * Do NOT use ->get() without selecting the columns explicitly,
         * otherwise the second "id" column can overwrite the employee ID.
         */
        $data = DB::table('tbl_people')
            ->join(
                'tbl_company_data',
                'tbl_people.id',
                '=',
                'tbl_company_data.reference'
            )
            ->select(
                'tbl_company_data.*',
                'tbl_people.*'
            )
            ->get();

        return response()->json($data);
    }


    /**
     * AJAX endpoint for company filtering.
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
                [
                    'rows' => $rowsHtml
                ]
            )
        );
    }


    /**
     * Shared employee query.
     *
     * CRITICAL:
     * tbl_people.id = tbl_company_data.reference
     *
     * tbl_people.* is selected LAST so $employee->id is always
     * the actual employee ID from tbl_people.
     */
    private function getEmployeesForCompany($companyId)
    {
        $q = table::people()
            ->join(
                'tbl_company_data',
                'tbl_people.id',
                '=',
                'tbl_company_data.reference'
            )
            ->select(
                'tbl_company_data.*',
                'tbl_people.*'
            );

        if ($companyId) {
            $companyRow = table::company()
                ->where('id', $companyId)
                ->first();

            $companyName = $companyRow
                ? mb_strtoupper(trim($companyRow->company))
                : null;

            $q->where(function ($sub) use ($companyId, $companyName) {

                $sub->where(
                    'tbl_company_data.company_id',
                    $companyId
                );

                if ($companyName) {
                    $sub->orWhereRaw(
                        'UPPER(TRIM(tbl_company_data.company)) = ?',
                        [$companyName]
                    );
                }
            });
        }

        return $q->get();
    }


    /**
     * Recompute summary cards.
     */
    private function buildSummaryCounts($data)
    {
        $total = $data->count();

        $active = $data
            ->where('employmentstatus', 'Active')
            ->count();

        $expired = $data->filter(function ($employee) {

            return $employee->visaend &&
                Carbon::parse($employee->visaend)->isPast();

        })->count();

        $expiring = $data->filter(function ($employee) {

            if (!$employee->visaend) {
                return false;
            }

            $days = now()->diffInDays(
                $employee->visaend,
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


    /**
     * New employee form.
     */
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
     * AJAX:
     * Get departments belonging to selected company.
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


    /**
     * Add employee.
     *
     * IMPORTANT — DO NOT rely on insertGetId()'s return value here.
     *
     * Something elsewhere in this app (a DB::listen() callback, an
     * activity logger, etc.) performs its own INSERT into another
     * table (tbl_activity_logs, confirmed via information_schema —
     * its AUTO_INCREMENT tracks closely with the bad reference
     * values we found in tbl_company_data) synchronously as a side
     * effect of every query, including the tbl_people insert below.
     * Because MySQL's LAST_INSERT_ID() is connection-scoped rather
     * than statement-scoped, that second insert overwrites the
     * value insertGetId() reads back afterward - so $refId would
     * silently become some unrelated activity_logs id instead of
     * the new employee's real tbl_people.id.
     *
     * Workaround: after inserting, re-select the row we just
     * created via 'nationalid' + 'emailaddress' (idno lives on
     * tbl_company_data, not tbl_people, so it can't be used here).
     * This makes tbl_company_data.reference correct regardless of
     * what is clobbering LAST_INSERT_ID() elsewhere in the app.
     *
     * TODO: find and fix the actual synchronous logger (search for
     * DB::listen(), ActivityLog, or an Observer bound in
     * AppServiceProvider::boot()) so insertGetId() can be trusted
     * again and this workaround can be removed. Also confirm
     * 'nationalid' is reliably unique in practice, or add a real
     * unique constraint/column to key this lookup on instead.
     */
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

            'employmentstatus' =>
                'required|alpha_dash_space|max:155',

            'company_id' =>
                'required|integer',

            'jobtitle_id' =>
                'nullable|integer|exists:tbl_form_jobtitle,id',

            'address_line' =>
                'nullable|array',

            'address_line.*' =>
                'nullable|string|max:500',

            'address_from' =>
                'nullable|array',

            'address_from.*' =>
                'nullable|date',

            'address_to' =>
                'nullable|array',

            'address_to.*' =>
                'nullable|date',

            'doc_reference' =>
                'nullable|array',

            'doc_reference.*' =>
                'nullable|string|max:255',

            'address_doc' =>
                'nullable|array',

            'address_doc.*' =>
                'nullable|file|mimes:jpeg,jpg,png,pdf|max:4096',

            'image' =>
                'nullable|image|mimes:jpeg,jpg,png|max:2048',

            'sharecodeexpiry' =>
                'nullable|required_with:sharecode|date',
        ]);


        /*
         * Validate company.
         */
        $companyRow = table::company()
            ->where('id', $request->company_id)
            ->first();

        if (!$companyRow) {
            return redirect('employees-new')
                ->withInput()
                ->with(
                    'error',
                    trans('Selected company is invalid.')
                );
        }

        $company = mb_strtoupper(
            trim($companyRow->company)
        );

        $companyId = $companyRow->id;


        /*
         * Validate job title.
         */
        $jobtitleId = null;

        if ($request->filled('jobtitle_id')) {

            $jobtitleRow = table::jobtitle()
                ->where('id', $request->jobtitle_id)
                ->first();

            if ($jobtitleRow) {
                $jobtitleId = $jobtitleRow->id;
            }
        }


        /*
         * Prepare employee data.
         */
        $lastname = mb_strtoupper(
            $request->lastname
        );

        $firstname = mb_strtoupper(
            $request->firstname
        );

        $mi = mb_strtoupper(
            $request->mi
        );

        $age = $request->age;

        $gender = mb_strtoupper(
            $request->gender
        );

        $emailaddress = mb_strtolower(
            $request->emailaddress
        );

        $civilstatus = mb_strtoupper(
            $request->civilstatus
        );

        $height = $request->height;
        $weight = $request->weight;
        $mobileno = $request->mobileno;

        $birthday = date(
            "Y-m-d",
            strtotime($request->birthday)
        );

        $nationalid = mb_strtoupper(
            $request->nationalid
        );

        $sharecode = $request->sharecode ?: null;

        $sharecodeexpiry = $sharecode
            ? $this->toNullableDate(
                $request->sharecodeexpiry
            )
            : null;

        $ni = $request->ni;

        $birthplace = mb_strtoupper(
            $request->birthplace
        );

        $homeaddress = mb_strtoupper(
            $request->homeaddress
        );

        $department = mb_strtoupper(
            $request->department
        );

        $jobposition = mb_strtoupper(
            $request->jobposition
        );

        $companyemail = mb_strtolower(
            $request->companyemail
        );

        $leaveprivilege = $request->leaveprivilege;

        $idno = mb_strtoupper(
            $request->idno
        );

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


        /*
         * Prevent duplicate employee ID number.
         *
         * NOTE: this uniqueness check is on tbl_company_data.idno,
         * not tbl_people - which is why the post-insert recovery
         * below keys off nationalid + emailaddress instead.
         */
        $is_idno_taken = table::companydata()
            ->where('idno', $idno)
            ->exists();

        if ($is_idno_taken) {
            return redirect('employees-new')
                ->withInput()
                ->with(
                    'error',
                    trans(
                        "Whoops! the ID Number is already taken."
                    )
                );
        }


        /*
         * Store address documents before DB transaction.
         */
        $addressDocPaths = [];

        try {

            foreach (
                $request->file('address_doc', [])
                as $i => $docFile
            ) {

                $addressDocPaths[$i] =
                    $this->storeAddressDocument(
                        $docFile
                    );
            }

        } catch (\RuntimeException $e) {

            return redirect('employees-new')
                ->withInput()
                ->with(
                    'error',
                    trans($e->getMessage())
                );
        }


        /*
         * Build address history.
         */
        $addressEntries = $this->buildAddressEntries(
            $request->input('address_line', []),
            $request->input('address_from', []),
            $request->input('address_to', []),
            $request->input('doc_reference', []),
            $addressDocPaths
        );


        /*
         * Store avatar.
         */
        $avatarPath = null;

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


        /*
         * ==========================================================
         * DATABASE TRANSACTION
         * ==========================================================
         */
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
                $request
            ) {

                /*
                 * ==================================================
                 * STEP 1:
                 * INSERT INTO tbl_people
                 *
                 * IMPORTANT - DO NOT TRUST THE RETURN VALUE:
                 *
                 * insertGetId() ultimately reads back MySQL's
                 * connection-scoped LAST_INSERT_ID(), which is being
                 * clobbered elsewhere in this app (see class-level
                 * docblock above). We still call insertGetId() to
                 * perform the insert, but we deliberately ignore
                 * what it returns.
                 * ==================================================
                 */
                table::people()->insertGetId([

                    'lastname' =>
                        $lastname,

                    'firstname' =>
                        $firstname,

                    'mi' =>
                        $mi,

                    'age' =>
                        $age,

                    'gender' =>
                        $gender,

                    'emailaddress' =>
                        $emailaddress,

                    'civilstatus' =>
                        $civilstatus,

                    'height' =>
                        $height,

                    'weight' =>
                        $weight,

                    'mobileno' =>
                        $mobileno,

                    'birthday' =>
                        $birthday,

                    'birthplace' =>
                        $birthplace,

                    'nationalid' =>
                        $nationalid,

                    'sharecode' =>
                        $sharecode,

                    'sharecode_expires_at' =>
                        $sharecodeexpiry,

                    'NI' =>
                        $ni,

                    'idissuedate' =>
                        $this->toNullableDate(
                            $request->idissuedate
                        ),

                    'idexpirydate' =>
                        $this->toNullableDate(
                            $request->idexpirydate
                        ),

                    'homeaddress' =>
                        $homeaddress,

                    'employmenttype' =>
                        $employmenttype,

                    'employmentstatus' =>
                        $employmentstatus,

                    'avatar' =>
                        $avatarPath,

                    'perhourpay' =>
                        $request->perhourpay,

                    'accountpay' =>
                        $request->accountpay,
                ]);


                /*
                 * ==================================================
                 * STEP 1b:
                 * RECOVER THE REAL tbl_people.id
                 *
                 * We just inserted exactly one row matching this
                 * nationalid + emailaddress combination. We order
                 * by id DESC as a defensive tie-breaker in case
                 * that combination is ever not perfectly unique.
                 * ==================================================
                 */
                $refId = table::people()
                    ->where('nationalid', $nationalid)
                    ->where('emailaddress', $emailaddress)
                    ->orderByDesc('id')
                    ->value('id');

                if (!$refId) {
                    throw new \RuntimeException(
                        'Could not recover the new employee record after insert.'
                    );
                }


                /*
                 * ==================================================
                 * STEP 2:
                 * INSERT tbl_company_data
                 *
                 * reference MUST be the real tbl_people.id recovered
                 * in STEP 1b above - NOT whatever insertGetId()
                 * returned.
                 * ==================================================
                 */
                table::companydata()->insert([

                    [

                        'reference' =>
                            $refId,

                        'company' =>
                            $company,

                        'company_id' =>
                            $companyId,

                        'jobtitle_id' =>
                            $jobtitleId,

                        'department' =>
                            $department,

                        'jobposition' =>
                            $jobposition,

                        'companyemail' =>
                            $companyemail,

                        'leaveprivilege' =>
                            $leaveprivilege,

                        'jobduties' =>
                            $request->jobduties,

                        'idno' =>
                            $idno,

                        'visaend' =>
                            $this->toNullableDate(
                                $request->visaend
                            ),

                        'visastart' =>
                            $this->toNullableDate(
                                $request->visastart
                            ),

                        'startdate' =>
                            $startdate,

                        'jobtype' =>
                            $request->jobtype,

                        'COSCertificateNo' =>
                            $request->COSCertificateNo,

                        'cosexpiry' =>
                            $this->toNullableDate(
                                $request->cosexpiry
                            ),

                        'visastatus' =>
                            $request->visastatus,

                        'kinno' =>
                            $request->kinno,

                        'kinname' =>
                            $request->kinname,

                        'workchecks' =>
                            $request->workchecks,

                        'dateregularized' =>
                            $dateregularized,
                    ]

                ]);


                /*
                 * ==================================================
                 * STEP 3:
                 * INSERT ADDRESS HISTORY
                 *
                 * Address history uses the SAME (correct) employee ID.
                 * ==================================================
                 */
                if (!empty($addressEntries)) {

                    $addressRows = [];

                    foreach (
                        $addressEntries as $entry
                    ) {

                        $addressRows[] = [

                            'reference' =>
                                $refId,

                            'address_line' =>
                                $entry['address'],

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

                            'created_at' =>
                                now(),

                            'updated_at' =>
                                now(),
                        ];
                    }

                    table::addresshistory()
                        ->insert($addressRows);
                }
            });


        } catch (\Exception $e) {

            /*
             * If DB transaction fails, remove uploaded files.
             */
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


            /*
             * Log real database error.
             */
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
                trans(
                    "New employee has been added!"
                )
            );
    }


    /**
     * Store employee avatar.
     */
    private function storeAvatarImage($file)
    {
        if (!$file) {
            return null;
        }

        /*
         * Verify the actual image contents.
         */
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


        /*
         * Never trust the original filename.
         */
        $filename =
            Str::uuid()->toString()
            . '.'
            . $extension;


        return $file->storeAs(
            'avatars',
            $filename,
            'public'
        );
    }


    /**
     * Store address supporting document.
     */
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
            Str::uuid()->toString()
            . '.'
            . $extension;


        return $file->storeAs(
            'address-documents',
            $filename,
            'public'
        );
    }


    /**
     * Build address history rows.
     */
    private function buildAddressEntries(
        array $addressLines,
        array $dateFrom,
        array $dateTo,
        array $docReferences = [],
        array $docFilePaths = []
    ) {

        $entries = [];

        foreach (
            $addressLines as $i => $line
        ) {

            $line = trim(
                (string) $line
            );


            /*
             * Ignore blank address lines.
             */
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
                    isset($docReferences[$i])
                    &&
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


    /**
     * Convert date input to nullable Y-m-d.
     */
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