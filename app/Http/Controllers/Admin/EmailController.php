<?php

namespace App\Http\Controllers\Admin;

use App\Classes\table;
use App\Classes\permission;
use App\Http\Controllers\Controller;
use App\Mail\CustomHrMail;
use App\Mail\DocumentExpiryReminderMail;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class EmailController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Expiry Settings
    |--------------------------------------------------------------------------
    |
    | These values control the colour/status shown in the Email Center.
    |
    | Passport / Visa:
    |   - Within 5 months = warning/danger
    |   - 20 days or less = danger
    |
    | Share Code:
    |   - Within 5 months = warning/danger
    |   - 6 days or less = danger
    |
    */

    private const MONTHS_MILESTONE = 5;
    private const DAYS_MILESTONE = 20;

    /**
     * GET /emails
     *
     * Email Center:
     * - Passport expiry
     * - Visa expiry
     * - Share Code expiry
     * - Custom HR email
     */
    public function index(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | Permission
        |--------------------------------------------------------------------------
        */
        if (permission::permitted('employees-edit') == 'fail') {
            return redirect()->route('denied');
        }

        /*
        |--------------------------------------------------------------------------
        | Companies
        |--------------------------------------------------------------------------
        */
        $companies = table::company()
            ->orderBy('company')
            ->get();

        $companyId = $request->query('company_id');

        $companyId = ($companyId !== null && is_numeric($companyId))
            ? (int) $companyId
            : null;

        /*
        |--------------------------------------------------------------------------
        | Get latest company record for each employee
        |--------------------------------------------------------------------------
        |
        | An employee can have multiple tbl_company_data records because of:
        |
        | - Transfer
        | - Promotion
        | - Department change
        | - Company change
        |
        | We only want the latest record.
        |
        */
        $latestCompanyDataIds = DB::table('tbl_company_data')
            ->select(DB::raw('MAX(id) as id'))
            ->groupBy('reference')
            ->pluck('id');

        /*
        |--------------------------------------------------------------------------
        | Employees
        |--------------------------------------------------------------------------
        |
        | LEFT JOIN is intentional.
        |
        | Passport and Share Code information exists on tbl_people and should
        | still appear even when an employee does not yet have a company record.
        |
        */
        $people = table::people()
            ->leftJoin('tbl_company_data', function ($join) use ($latestCompanyDataIds) {
                $join->on(
                    'tbl_company_data.reference',
                    '=',
                    'tbl_people.id'
                )->whereIn(
                    'tbl_company_data.id',
                    $latestCompanyDataIds
                );
            })
            ->where('tbl_people.employmentstatus', 'Active')
            ->when(
                $companyId,
                fn ($q) => $q->where(
                    'tbl_company_data.company_id',
                    $companyId
                )
            )
            ->orderBy('tbl_people.lastname')
            ->get([
                'tbl_people.id',
                'tbl_people.firstname',
                'tbl_people.lastname',
                'tbl_people.emailaddress',

                /*
                |--------------------------------------------------------------------------
                | Passport
                |--------------------------------------------------------------------------
                */
                'tbl_people.nationalid',
                'tbl_people.idexpirydate',

                /*
                |--------------------------------------------------------------------------
                | Share Code
                |--------------------------------------------------------------------------
                */
                'tbl_people.sharecode',
                'tbl_people.sharecode_expires_at',

                /*
                |--------------------------------------------------------------------------
                | Visa
                |--------------------------------------------------------------------------
                */
                'tbl_company_data.visaend',
                'tbl_company_data.company',
            ]);

        /*
        |--------------------------------------------------------------------------
        | Today
        |--------------------------------------------------------------------------
        |
        | IMPORTANT:
        | startOfDay() means we compare calendar dates instead of timestamps.
        |
        */
        $today = Carbon::today();

        /*
        |--------------------------------------------------------------------------
        | Passport List
        |--------------------------------------------------------------------------
        */
        $passportList = $people
            ->filter(function ($person) {
                return !empty($person->idexpirydate);
            })
            ->map(function ($person) use ($today) {
                $person->expiryInfo = $this->expiryInfo(
                    $person->idexpirydate,
                    $today,
                    'passport_expiry'
                );

                return $person;
            })
            ->sortBy(function ($person) {
                return $person->expiryInfo['sortValue'];
            })
            ->values();

        /*
        |--------------------------------------------------------------------------
        | Visa List
        |--------------------------------------------------------------------------
        */
        $visaList = $people
            ->filter(function ($person) {
                return !empty($person->visaend);
            })
            ->map(function ($person) use ($today) {
                $person->expiryInfo = $this->expiryInfo(
                    $person->visaend,
                    $today,
                    'visa_expiry'
                );

                return $person;
            })
            ->sortBy(function ($person) {
                return $person->expiryInfo['sortValue'];
            })
            ->values();

        /*
        |--------------------------------------------------------------------------
        | Share Code List
        |--------------------------------------------------------------------------
        */
        $shareCodeList = $people
            ->filter(function ($person) {
                return !empty($person->sharecode_expires_at);
            })
            ->map(function ($person) use ($today) {
                $person->expiryInfo = $this->expiryInfo(
                    $person->sharecode_expires_at,
                    $today,
                    'sharecode_expiry'
                );

                return $person;
            })
            ->sortBy(function ($person) {
                return $person->expiryInfo['sortValue'];
            })
            ->values();

        /*
        |--------------------------------------------------------------------------
        | Last Sent Emails
        |--------------------------------------------------------------------------
        */
        $lastSent = DB::table('email_logs')
            ->select(
                'reference',
                'type',
                DB::raw('MAX(created_at) as last_sent_at')
            )
            ->whereIn('type', [
                'passport_expiry',
                'visa_expiry',
                'sharecode_expiry',
            ])
            ->groupBy('reference', 'type')
            ->get()
            ->groupBy('reference');

        return view(
            'admin.emails.index',
            compact(
                'companies',
                'companyId',
                'passportList',
                'visaList',
                'shareCodeList',
                'people',
                'lastSent'
            )
        );
    }

    /**
     * POST /emails/passport/{id}
     *
     * Send passport reminder immediately.
     */
    public function sendPassportReminder(Request $request, $id)
    {
        if (permission::permitted('employees-edit') == 'fail') {
            return $this->denyResponse($request);
        }

        /*
        |--------------------------------------------------------------------------
        | Employee
        |--------------------------------------------------------------------------
        */
        $person = table::people()
            ->where('id', $id)
            ->first();

        if (!$person || empty($person->idexpirydate)) {
            return $this->respond(
                $request,
                false,
                trans('This employee has no passport expiry date on file.')
            );
        }

        if (empty($person->emailaddress)) {
            return $this->respond(
                $request,
                false,
                trans('This employee has no email address on file.')
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Application Name
        |--------------------------------------------------------------------------
        */
        $appSettings = table::settings()
            ->where('id', 1)
            ->first();

        $appName = !empty($appSettings->app_name)
            ? $appSettings->app_name
            : 'Company';

        /*
        |--------------------------------------------------------------------------
        | Correct Calendar Date Calculation
        |--------------------------------------------------------------------------
        */
        $expiry = $this->parseExpiryDate($person->idexpirydate);
        $today = Carbon::today();

        $daysRemaining = $this->daysBetweenDates(
            $today,
            $expiry
        );

        $employeeName = mb_strtoupper(
            $person->lastname . ', ' . $person->firstname
        );

        $subject = "Passport Expiry Reminder - {$appName}";

        try {
            Mail::to($person->emailaddress)->send(
                new DocumentExpiryReminderMail(
                    $employeeName,
                    'Passport',
                    $person->nationalid,
                    $expiry->format('d F Y'),
                    $daysRemaining,
                    $appName
                )
            );

            $this->logEmail(
                $person->id,
                'passport_expiry',
                null,
                $expiry->format('Y-m-d'),
                $person->emailaddress,
                $subject,
                'sent',
                null
            );

            return $this->respond(
                $request,
                true,
                trans('Passport reminder email sent to') .
                ' ' .
                $employeeName .
                '.'
            );

        } catch (\Exception $e) {

            Log::error(
                'Failed to send manual passport reminder for #' .
                $id .
                ': ' .
                $e->getMessage()
            );

            $this->logEmail(
                $person->id,
                'passport_expiry',
                null,
                $expiry->format('Y-m-d'),
                $person->emailaddress,
                $subject,
                'failed',
                $e->getMessage()
            );

            return $this->respond(
                $request,
                false,
                trans(
                    'Something went wrong while sending the email. Please try again.'
                )
            );
        }
    }

    /**
     * POST /emails/visa/{id}
     *
     * Send visa reminder immediately.
     */
    public function sendVisaReminder(Request $request, $id)
    {
        if (permission::permitted('employees-edit') == 'fail') {
            return $this->denyResponse($request);
        }

        /*
        |--------------------------------------------------------------------------
        | Latest Company Record
        |--------------------------------------------------------------------------
        */
        $companyData = table::companydata()
            ->where('reference', $id)
            ->orderByDesc('id')
            ->first();

        $person = table::people()
            ->where('id', $id)
            ->first();

        if (!$person || !$companyData || empty($companyData->visaend)) {
            return $this->respond(
                $request,
                false,
                trans('This employee has no visa expiry date on file.')
            );
        }

        if (empty($person->emailaddress)) {
            return $this->respond(
                $request,
                false,
                trans('This employee has no email address on file.')
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Application Name
        |--------------------------------------------------------------------------
        */
        $appSettings = table::settings()
            ->where('id', 1)
            ->first();

        $appName = !empty($appSettings->app_name)
            ? $appSettings->app_name
            : 'Company';

        /*
        |--------------------------------------------------------------------------
        | Correct Calendar Date Calculation
        |--------------------------------------------------------------------------
        */
        $expiry = $this->parseExpiryDate($companyData->visaend);
        $today = Carbon::today();

        $daysRemaining = $this->daysBetweenDates(
            $today,
            $expiry
        );

        $employeeName = mb_strtoupper(
            $person->lastname . ', ' . $person->firstname
        );

        $subject = "Visa Expiry Reminder - {$appName}";

        try {
            Mail::to($person->emailaddress)->send(
                new DocumentExpiryReminderMail(
                    $employeeName,
                    'Visa',
                    null,
                    $expiry->format('d F Y'),
                    $daysRemaining,
                    $appName
                )
            );

            $this->logEmail(
                $person->id,
                'visa_expiry',
                null,
                $expiry->format('Y-m-d'),
                $person->emailaddress,
                $subject,
                'sent',
                null
            );

            return $this->respond(
                $request,
                true,
                trans('Visa reminder email sent to') .
                ' ' .
                $employeeName .
                '.'
            );

        } catch (\Exception $e) {

            Log::error(
                'Failed to send manual visa reminder for #' .
                $id .
                ': ' .
                $e->getMessage()
            );

            $this->logEmail(
                $person->id,
                'visa_expiry',
                null,
                $expiry->format('Y-m-d'),
                $person->emailaddress,
                $subject,
                'failed',
                $e->getMessage()
            );

            return $this->respond(
                $request,
                false,
                trans(
                    'Something went wrong while sending the email. Please try again.'
                )
            );
        }
    }

    /**
     * POST /emails/sharecode/{id}
     *
     * Send Share Code reminder immediately.
     */
    public function sendShareCodeReminder(Request $request, $id)
    {
        if (permission::permitted('employees-edit') == 'fail') {
            return $this->denyResponse($request);
        }

        /*
        |--------------------------------------------------------------------------
        | Employee
        |--------------------------------------------------------------------------
        */
        $person = table::people()
            ->where('id', $id)
            ->first();

        if (!$person || empty($person->sharecode_expires_at)) {
            return $this->respond(
                $request,
                false,
                trans('This employee has no share code expiry date on file.')
            );
        }

        if (empty($person->emailaddress)) {
            return $this->respond(
                $request,
                false,
                trans('This employee has no email address on file.')
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Application Name
        |--------------------------------------------------------------------------
        */
        $appSettings = table::settings()
            ->where('id', 1)
            ->first();

        $appName = !empty($appSettings->app_name)
            ? $appSettings->app_name
            : 'Company';

        /*
        |--------------------------------------------------------------------------
        | Correct Calendar Date Calculation
        |--------------------------------------------------------------------------
        |
        | Share code may be stored as:
        |
        | 2028-02-21
        |
        | OR:
        |
        | 2028-02-21 23:59:59
        |
        | We intentionally treat it as an expiry DATE.
        |
        */
        $expiry = $this->parseExpiryDate(
            $person->sharecode_expires_at
        );

        $today = Carbon::today();

        $daysRemaining = $this->daysBetweenDates(
            $today,
            $expiry
        );

        $employeeName = mb_strtoupper(
            $person->lastname . ', ' . $person->firstname
        );

        $subject = "Share Code Expiry Reminder - {$appName}";

        try {
            Mail::to($person->emailaddress)->send(
                new DocumentExpiryReminderMail(
                    $employeeName,
                    'Share Code',
                    $person->sharecode,
                    $expiry->format('d F Y'),
                    $daysRemaining,
                    $appName
                )
            );

            $this->logEmail(
                $person->id,
                'sharecode_expiry',
                null,
                $expiry->format('Y-m-d'),
                $person->emailaddress,
                $subject,
                'sent',
                null
            );

            return $this->respond(
                $request,
                true,
                trans('Share code reminder email sent to') .
                ' ' .
                $employeeName .
                '.'
            );

        } catch (\Exception $e) {

            Log::error(
                'Failed to send manual share code reminder for #' .
                $id .
                ': ' .
                $e->getMessage()
            );

            $this->logEmail(
                $person->id,
                'sharecode_expiry',
                null,
                $expiry->format('Y-m-d'),
                $person->emailaddress,
                $subject,
                'failed',
                $e->getMessage()
            );

            return $this->respond(
                $request,
                false,
                trans(
                    'Something went wrong while sending the email. Please try again.'
                )
            );
        }
    }

    /**
     * POST /emails/custom
     *
     * Send general HR email to selected employees.
     */
    public function sendCustom(Request $request)
    {
        if (permission::permitted('employees-edit') == 'fail') {
            return $this->denyResponse($request);
        }

        /*
        |--------------------------------------------------------------------------
        | Validation
        |--------------------------------------------------------------------------
        */
        $request->validate([
            'employee_ids' => 'required|array|min:1',
            'employee_ids.*' => 'integer|exists:tbl_people,id',
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:5000',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Application Settings
        |--------------------------------------------------------------------------
        */
        $appSettings = table::settings()
            ->where('id', 1)
            ->first();

        $appName = !empty($appSettings->app_name)
            ? $appSettings->app_name
            : 'Company';

        $senderName = auth()->user()->name ?? 'HR';

        $sentCount = 0;
        $skipped = 0;

        /*
        |--------------------------------------------------------------------------
        | Send Emails
        |--------------------------------------------------------------------------
        */
        foreach ($request->employee_ids as $employeeId) {

            $person = table::people()
                ->where('id', $employeeId)
                ->first();

            if (!$person || empty($person->emailaddress)) {
                $skipped++;
                continue;
            }

            $employeeName = mb_strtoupper(
                $person->lastname . ', ' . $person->firstname
            );

            try {

                Mail::to($person->emailaddress)->send(
                    new CustomHrMail(
                        $employeeName,
                        $request->subject,
                        $request->message,
                        $senderName,
                        $appName
                    )
                );

                $this->logEmail(
                    $person->id,
                    'custom',
                    null,
                    null,
                    $person->emailaddress,
                    $request->subject,
                    'sent',
                    null
                );

                $sentCount++;

            } catch (\Exception $e) {

                Log::error(
                    'Failed to send custom HR email to #' .
                    $employeeId .
                    ': ' .
                    $e->getMessage()
                );

                $this->logEmail(
                    $person->id,
                    'custom',
                    null,
                    null,
                    $person->emailaddress,
                    $request->subject,
                    'failed',
                    $e->getMessage()
                );

                $skipped++;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Response Message
        |--------------------------------------------------------------------------
        */
        $message = trans('Email sent to') .
            ' ' .
            $sentCount .
            ' ' .
            trans('employee(s).');

        if ($skipped > 0) {
            $message .=
                ' ' .
                $skipped .
                ' ' .
                trans(
                    'were skipped (no email on file, or the send failed).'
                );
        }

        return $this->respond(
            $request,
            $sentCount > 0,
            $message
        );
    }

    /**
     * Parse an expiry value as a calendar date.
     *
     * This is intentionally based on the DATE portion.
     *
     * Examples:
     *
     * 2028-02-21
     * 2028-02-21 23:59:59
     * 2028-02-21T23:59:59
     *
     * All become:
     *
     * 2028-02-21 00:00:00
     */
    private function parseExpiryDate($date): Carbon
    {
        return Carbon::parse($date)->startOfDay();
    }

    /**
     * Calculate signed calendar-day difference.
     *
     * Positive:
     *   expiry is in the future
     *
     * Zero:
     *   expiry is today
     *
     * Negative:
     *   expiry is in the past
     */
    private function daysBetweenDates(
        Carbon $today,
        Carbon $expiry
    ): int {
        $today = $today->copy()->startOfDay();
        $expiry = $expiry->copy()->startOfDay();

        return (int) $today->diffInDays(
            $expiry,
            false
        );
    }

    /**
     * Shared expiry status logic.
     *
     * Used by:
     * - Passport
     * - Visa
     * - Share Code
     */
    private function expiryInfo(
        $date,
        Carbon $today,
        string $type = 'default'
    ): array {

        /*
        |--------------------------------------------------------------------------
        | Empty Date
        |--------------------------------------------------------------------------
        */
        if (empty($date)) {
            return [
                'class' => 'bg-secondary',
                'text' => trans('No expiry date'),
                'expiryDate' => null,
                'sortValue' => PHP_INT_MAX,
                'daysRemaining' => null,
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Normalize both dates to calendar dates
        |--------------------------------------------------------------------------
        */
        $today = $today->copy()->startOfDay();
        $expiry = $this->parseExpiryDate($date);

        /*
        |--------------------------------------------------------------------------
        | Signed calendar-day difference
        |--------------------------------------------------------------------------
        |
        | This remains the numeric value used for sorting and threshold checks.
        |
        */
        $daysRemaining = $this->daysBetweenDates(
            $today,
            $expiry
        );

        /*
        |--------------------------------------------------------------------------
        | EXPIRED
        |--------------------------------------------------------------------------
        */
        if ($daysRemaining < 0) {

            $daysAgo = abs($daysRemaining);

            return [
                'class' => 'bg-danger',

                'text' => trans('Expired') .
                    ' (' .
                    $this->formatExpiryDuration(
                        $expiry,
                        $today,
                        true
                    ) .
                    ')',

                'expiryDate' => $expiry->format('d F Y'),
                'sortValue' => $daysRemaining,
                'daysRemaining' => $daysRemaining,
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | EXPIRES TODAY
        |--------------------------------------------------------------------------
        */
        if ($daysRemaining === 0) {

            return [
                'class' => 'bg-danger',
                'text' => trans('Expires today'),
                'expiryDate' => $expiry->format('d F Y'),
                'sortValue' => 0,
                'daysRemaining' => 0,
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Danger Threshold
        |--------------------------------------------------------------------------
        |
        | Passport / Visa:
        |   20 days or less = danger
        |
        | Share Code:
        |   6 days or less = danger
        |
        */
        $dangerDaysThreshold =
            $type === 'sharecode_expiry'
                ? 6
                : self::DAYS_MILESTONE;

        /*
        |--------------------------------------------------------------------------
        | Five-Month Milestone
        |--------------------------------------------------------------------------
        */
        $milestoneDate = $today->copy()
            ->addMonthsNoOverflow(self::MONTHS_MILESTONE)
            ->startOfDay();

        /*
        |--------------------------------------------------------------------------
        | Colour
        |--------------------------------------------------------------------------
        */
        if ($expiry->lessThanOrEqualTo($milestoneDate)) {

            if ($daysRemaining <= $dangerDaysThreshold) {
                $class = 'bg-danger';
            } else {
                $class = 'bg-warning';
            }

        } else {
            $class = 'bg-success';
        }

        /*
        |--------------------------------------------------------------------------
        | Remaining Expiry
        |--------------------------------------------------------------------------
        |
        | Display a human calendar duration:
        |
        | 6 days remaining
        | 6 months 2 days remaining
        | 1 year 5 months 28 days remaining
        |
        | The actual day difference is still retained separately for sorting.
        |
        */
        return [
            'class' => $class,

            'text' => $this->formatExpiryDuration(
                $expiry,
                $today
            ) . ' ' . trans('remaining'),

            'expiryDate' => $expiry->format('d F Y'),
            'sortValue' => $daysRemaining,
            'daysRemaining' => $daysRemaining,
        ];
    }

    /**
     * Format the remaining expiry as calendar years/months/days.
     *
     * Examples:
     *
     * 6 days remaining
     * 6 months 2 days remaining
     * 1 year 5 months 28 days remaining
     */
    private function formatExpiryDuration(
        Carbon $expiry,
        Carbon $today,
        bool $expired = false
    ): string {

        $today = $today->copy()->startOfDay();
        $expiry = $expiry->copy()->startOfDay();

        $from = $expired ? $expiry : $today;
        $to = $expired ? $today : $expiry;

        $diff = $from->diff($to);

        $parts = [];

        if ($diff->y > 0) {
            $parts[] = $diff->y . ' ' .
                ($diff->y === 1 ? trans('year') : trans('years'));
        }

        if ($diff->m > 0) {
            $parts[] = $diff->m . ' ' .
                ($diff->m === 1 ? trans('month') : trans('months'));
        }

        if ($diff->d > 0) {
            $parts[] = $diff->d . ' ' .
                ($diff->d === 1 ? trans('day') : trans('days'));
        }

        /*
        |--------------------------------------------------------------------------
        | Safety fallback
        |--------------------------------------------------------------------------
        */
        if (empty($parts)) {
            return '0 ' . trans('days');
        }

        return implode(' ', $parts);
    }

    /**
     * AJAX/JSON or normal redirect response.
     */
    private function respond(
        Request $request,
        bool $success,
        string $message
    ) {
        if (
            $request->expectsJson() ||
            $request->ajax()
        ) {
            return response()->json(
                [
                    'success' => $success,
                    'message' => $message,
                ],
                $success ? 200 : 422
            );
        }

        return $success
            ? back()->with('success', $message)
            : back()->with('error', $message);
    }

    /**
     * Permission denied response.
     */
    private function denyResponse(Request $request)
    {
        if (
            $request->expectsJson() ||
            $request->ajax()
        ) {
            return response()->json(
                [
                    'success' => false,
                    'message' => trans(
                        'You do not have permission to do this.'
                    ),
                ],
                403
            );
        }

        return redirect()->route('denied');
    }

    /**
     * Save email log.
     */
    private function logEmail(
        $reference,
        $type,
        $milestone,
        $documentDate,
        $toEmail,
        $subject,
        $status,
        $errorMessage
    ): void {

        DB::table('email_logs')->insert([
            'reference' => $reference,
            'type' => $type,
            'milestone' => $milestone,
            'document_date' => $documentDate,
            'to_email' => $toEmail,
            'subject' => $subject,
            'sent_by' => auth()->id(),
            'status' => $status,

            'error' => $errorMessage
                ? substr($errorMessage, 0, 500)
                : null,

            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}