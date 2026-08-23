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
use Illuminate\Support\Facades\Mail;

class EmailController extends Controller
{
    // Same lead-time windows as the automatic scheduler, used here only
    // to colour-code the lists - manual sends below always fire
    // immediately regardless of these windows.
    private const MONTHS_MILESTONE = 5;
    private const DAYS_MILESTONE = 20;

    /**
     * GET /emails - Email Center: passport, visa, and share code expiry
     * lists (with manual send buttons) plus a compose form for custom
     * HR emails.
     */
    public function index(Request $request)
    {
        if (permission::permitted('employees-edit') == 'fail') { return redirect()->route('denied'); }

        $companies = table::company()->orderBy('company')->get();

        $companyId = $request->query('company_id');
        $companyId = ($companyId !== null && is_numeric($companyId)) ? (int) $companyId : null;

        $people = table::people()
            ->join('tbl_company_data', 'tbl_company_data.reference', '=', 'tbl_people.id')
            ->where('tbl_people.employmentstatus', 'Active')
            ->when($companyId, fn ($q) => $q->where('tbl_company_data.company_id', $companyId))
            ->orderBy('tbl_people.lastname')
            ->get([
                'tbl_people.id', 'tbl_people.firstname', 'tbl_people.lastname', 'tbl_people.emailaddress',
                // Passport: nationalid is the passport NUMBER (display
                // only), idexpirydate is the passport's own expiry date
                // and is what the countdown is computed from.
                'tbl_people.nationalid', 'tbl_people.idexpirydate',
                // Share Code: sharecode is the code itself (display
                // only), sharecode_expires_at drives the countdown.
                'tbl_people.sharecode', 'tbl_people.sharecode_expires_at',
                'tbl_company_data.visaend', 'tbl_company_data.company',
            ]);

        $today = Carbon::now()->startOfDay();

        $passportList = $people->filter(fn ($p) => !empty($p->idexpirydate))
            ->map(function ($p) use ($today) {
                $p->expiryInfo = $this->expiryInfo($p->idexpirydate, $today);
                return $p;
            })
            ->sortBy(fn ($p) => $p->expiryInfo['sortValue'])
            ->values();

        $visaList = $people->filter(fn ($p) => !empty($p->visaend))
            ->map(function ($p) use ($today) {
                $p->expiryInfo = $this->expiryInfo($p->visaend, $today);
                return $p;
            })
            ->sortBy(fn ($p) => $p->expiryInfo['sortValue'])
            ->values();

        $shareCodeList = $people->filter(fn ($p) => !empty($p->sharecode_expires_at))
            ->map(function ($p) use ($today) {
                $p->expiryInfo = $this->expiryInfo($p->sharecode_expires_at, $today, 'sharecode_expiry');
                return $p;
            })
            ->sortBy(fn ($p) => $p->expiryInfo['sortValue'])
            ->values();

        // Small "last sent" hint per employee/type in the UI.
        $lastSent = DB::table('email_logs')
            ->select('reference', 'type', DB::raw('MAX(created_at) as last_sent_at'))
            ->whereIn('type', ['passport_expiry', 'visa_expiry', 'sharecode_expiry'])
            ->groupBy('reference', 'type')
            ->get()
            ->groupBy('reference');

        return view('admin.emails.index', compact(
            'companies', 'companyId', 'passportList', 'visaList', 'shareCodeList', 'people', 'lastSent'
        ));
    }

    /**
     * POST /emails/passport/{id} - manual "send now" for one employee's
     * passport reminder, regardless of how far out the expiry actually is.
     */
    public function sendPassportReminder(Request $request, $id)
    {
        if (permission::permitted('employees-edit') == 'fail') { return $this->denyResponse($request); }

        $person = table::people()->where('id', $id)->first();

        if (!$person || empty($person->idexpirydate)) {
            return $this->respond($request, false, trans('This employee has no passport expiry date on file.'));
        }

        if (empty($person->emailaddress)) {
            return $this->respond($request, false, trans('This employee has no email address on file.'));
        }

        $appSettings = table::settings()->where('id', 1)->first();
        $appName = !empty($appSettings->app_name) ? $appSettings->app_name : 'Company';

        $expiry = Carbon::parse($person->idexpirydate)->startOfDay();
        $today = Carbon::now()->startOfDay();
        $daysRemaining = (int) $today->diffInDays($expiry, false);

        $employeeName = mb_strtoupper($person->lastname.', '.$person->firstname);
        $subject = "Passport Expiry Reminder - {$appName}";

        try {
            Mail::to($person->emailaddress)->send(new DocumentExpiryReminderMail(
                $employeeName, 'Passport', $person->nationalid, $expiry->format('d F Y'), $daysRemaining, $appName
            ));

            $this->logEmail($person->id, 'passport_expiry', null, $expiry->format('Y-m-d'), $person->emailaddress, $subject, 'sent', null);

            return $this->respond($request, true, trans('Passport reminder email sent to').' '.$employeeName.'.');
        } catch (\Exception $e) {
            \Log::error('Failed to send manual passport reminder for #'.$id.': '.$e->getMessage());
            $this->logEmail($person->id, 'passport_expiry', null, $expiry->format('Y-m-d'), $person->emailaddress, $subject, 'failed', $e->getMessage());

            return $this->respond($request, false, trans('Something went wrong while sending the email. Please try again.'));
        }
    }

    /**
     * POST /emails/visa/{id} - manual "send now" for one employee's
     * visa reminder.
     */
    public function sendVisaReminder(Request $request, $id)
    {
        if (permission::permitted('employees-edit') == 'fail') { return $this->denyResponse($request); }

        $companyData = table::companydata()->where('reference', $id)->first();
        $person = table::people()->where('id', $id)->first();

        if (!$person || !$companyData || empty($companyData->visaend)) {
            return $this->respond($request, false, trans('This employee has no visa expiry date on file.'));
        }

        if (empty($person->emailaddress)) {
            return $this->respond($request, false, trans('This employee has no email address on file.'));
        }

        $appSettings = table::settings()->where('id', 1)->first();
        $appName = !empty($appSettings->app_name) ? $appSettings->app_name : 'Company';

        $expiry = Carbon::parse($companyData->visaend)->startOfDay();
        $today = Carbon::now()->startOfDay();
        $daysRemaining = (int) $today->diffInDays($expiry, false);

        $employeeName = mb_strtoupper($person->lastname.', '.$person->firstname);
        $subject = "Visa Expiry Reminder - {$appName}";

        try {
            Mail::to($person->emailaddress)->send(new DocumentExpiryReminderMail(
                $employeeName, 'Visa', null, $expiry->format('d F Y'), $daysRemaining, $appName
            ));

            $this->logEmail($person->id, 'visa_expiry', null, $expiry->format('Y-m-d'), $person->emailaddress, $subject, 'sent', null);

            return $this->respond($request, true, trans('Visa reminder email sent to').' '.$employeeName.'.');
        } catch (\Exception $e) {
            \Log::error('Failed to send manual visa reminder for #'.$id.': '.$e->getMessage());
            $this->logEmail($person->id, 'visa_expiry', null, $expiry->format('Y-m-d'), $person->emailaddress, $subject, 'failed', $e->getMessage());

            return $this->respond($request, false, trans('Something went wrong while sending the email. Please try again.'));
        }
    }

    /**
     * POST /emails/sharecode/{id} - manual "send now" for one employee's
     * share code reminder.
     */
    public function sendShareCodeReminder(Request $request, $id)
    {
        if (permission::permitted('employees-edit') == 'fail') { return $this->denyResponse($request); }

        $person = table::people()->where('id', $id)->first();

        if (!$person || empty($person->sharecode_expires_at)) {
            return $this->respond($request, false, trans('This employee has no share code expiry date on file.'));
        }

        if (empty($person->emailaddress)) {
            return $this->respond($request, false, trans('This employee has no email address on file.'));
        }

        $appSettings = table::settings()->where('id', 1)->first();
        $appName = !empty($appSettings->app_name) ? $appSettings->app_name : 'Company';

        $expiry = Carbon::parse($person->sharecode_expires_at)->startOfDay();
        $today = Carbon::now()->startOfDay();
        $daysRemaining = (int) $today->diffInDays($expiry, false);

        $employeeName = mb_strtoupper($person->lastname.', '.$person->firstname);
        $subject = "Share Code Expiry Reminder - {$appName}";

        try {
            Mail::to($person->emailaddress)->send(new DocumentExpiryReminderMail(
                $employeeName, 'Share Code', $person->sharecode, $expiry->format('d F Y'), $daysRemaining, $appName
            ));

            $this->logEmail($person->id, 'sharecode_expiry', null, $expiry->format('Y-m-d'), $person->emailaddress, $subject, 'sent', null);

            return $this->respond($request, true, trans('Share code reminder email sent to').' '.$employeeName.'.');
        } catch (\Exception $e) {
            \Log::error('Failed to send manual share code reminder for #'.$id.': '.$e->getMessage());
            $this->logEmail($person->id, 'sharecode_expiry', null, $expiry->format('Y-m-d'), $person->emailaddress, $subject, 'failed', $e->getMessage());

            return $this->respond($request, false, trans('Something went wrong while sending the email. Please try again.'));
        }
    }

    /**
     * POST /emails/custom - compose and send a general-purpose HR
     * message to one or more selected employees.
     */
    public function sendCustom(Request $request)
    {
        if (permission::permitted('employees-edit') == 'fail') { return $this->denyResponse($request); }

        // Laravel automatically returns a 422 JSON error response for
        // any request that expects JSON (which the AJAX call below
        // sets via dataType: 'json') instead of redirecting, so no
        // special handling is needed here for the AJAX case.
        $request->validate([
            'employee_ids' => 'required|array|min:1',
            'employee_ids.*' => 'integer|exists:tbl_people,id',
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:5000',
        ]);

        $appSettings = table::settings()->where('id', 1)->first();
        $appName = !empty($appSettings->app_name) ? $appSettings->app_name : 'Company';
        $senderName = auth()->user()->name ?? 'HR';

        $sentCount = 0;
        $skipped = 0;

        foreach ($request->employee_ids as $employeeId) {
            $person = table::people()->where('id', $employeeId)->first();

            if (!$person || empty($person->emailaddress)) {
                $skipped++;
                continue;
            }

            $employeeName = mb_strtoupper($person->lastname.', '.$person->firstname);

            try {
                Mail::to($person->emailaddress)->send(new CustomHrMail(
                    $employeeName, $request->subject, $request->message, $senderName, $appName
                ));

                $this->logEmail($person->id, 'custom', null, null, $person->emailaddress, $request->subject, 'sent', null);

                $sentCount++;
            } catch (\Exception $e) {
                \Log::error('Failed to send custom HR email to #'.$employeeId.': '.$e->getMessage());
                $this->logEmail($person->id, 'custom', null, null, $person->emailaddress, $request->subject, 'failed', $e->getMessage());

                $skipped++;
            }
        }

        $message = trans('Email sent to').' '.$sentCount.' '.trans('employee(s).');
        if ($skipped > 0) {
            $message .= ' '.$skipped.' '.trans('were skipped (no email on file, or the send failed).');
        }

        // Treated as a "soft success" (200, success:true) even when some
        // recipients were skipped, since at least the request itself
        // completed - the message text already tells the user how many
        // were skipped and why. Only a total wipeout (0 sent) reads as
        // a failure toast.
        return $this->respond($request, $sentCount > 0, $message);
    }

    /**
     * Uniform AJAX/JSON vs classic redirect+flash response for every
     * manual send action above. The Email Center view now submits all
     * of these via AJAX and reads {success, message} back to drive a
     * $.notify() toast - but this still degrades gracefully to the old
     * session-flash redirect if any of these routes are ever hit as a
     * plain (non-AJAX) form post.
     */
    private function respond(Request $request, bool $success, string $message)
    {
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['success' => $success, 'message' => $message], $success ? 200 : 422);
        }

        return $success ? back()->with('success', $message) : back()->with('error', $message);
    }

    /**
     * Same AJAX/redirect split as respond() above, for the
     * permission-denied case specifically.
     */
    private function denyResponse(Request $request)
    {
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['success' => false, 'message' => trans('You do not have permission to do this.')], 403);
        }

        return redirect()->route('denied');
    }

    /**
     * Shared colour/label logic for the passport, visa & share code
     * lists - mirrors the thresholds ProfileController/profile-view
     * already use, plus a sortValue so expired documents always float
     * to the top.
     *
     * @param string $type 'default', or 'sharecode_expiry' to use the
     *        tighter 6-day danger threshold that matches the extra
     *        close-to-expiry milestone SendExpiryReminders sends for
     *        share codes specifically (see dayMilestonesFor() there).
     */
    private function expiryInfo($date, Carbon $today, string $type = 'default'): array
    {
        $expiry = Carbon::parse($date)->startOfDay();

        if ($expiry->isPast()) {
            $daysAgo = (int) $expiry->diffInDays($today);
            return [
                'class' => 'bg-danger',
                'text' => trans('Expired').' ('.$daysAgo.' '.trans('days ago').')',
                'expiryDate' => $expiry->format('d F Y'),
                'sortValue' => -999999 + $daysAgo, // most overdue first
            ];
        }

        $days = (int) $today->diffInDays($expiry);
        $months = (int) $today->diffInMonths($expiry);

        // Share codes tend to have much shorter validity windows than a
        // passport or visa, so "urgent" for one means something closer
        // to expiry than for the other.
        $dangerDaysThreshold = $type === 'sharecode_expiry' ? 6 : self::DAYS_MILESTONE;

        $class = $months <= self::MONTHS_MILESTONE
            ? ($days <= $dangerDaysThreshold ? 'bg-danger' : 'bg-warning')
            : 'bg-success';

        return [
            'class' => $class,
            'text' => $days.' '.trans('days remaining'),
            'expiryDate' => $expiry->format('d F Y'),
            'sortValue' => $days,
        ];
    }

    /**
     * Small helper to keep the repeated email_logs insert (identical
     * columns every time, just different values) out of every manual
     * send method above.
     */
    private function logEmail($reference, $type, $milestone, $documentDate, $toEmail, $subject, $status, $errorMessage): void
    {
        DB::table('email_logs')->insert([
            'reference' => $reference,
            'type' => $type,
            'milestone' => $milestone,
            'document_date' => $documentDate,
            'to_email' => $toEmail,
            'subject' => $subject,
            'sent_by' => auth()->id(),
            'status' => $status,
            'error' => $errorMessage ? substr($errorMessage, 0, 500) : null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}