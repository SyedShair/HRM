<?php

namespace App\Console\Commands;

use App\Classes\table;
use App\Mail\DocumentExpiryReminderMail;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class SendExpiryReminders extends Command
{
    protected $signature = 'emails:send-expiry-reminders';

    protected $description = 'Send automatic daily passport, visa, and share code expiry reminder emails when 2 months or less remain.';

    /**
     * Start sending daily reminders when this many months remain.
     */
    private const DAILY_REMINDER_MONTHS = 2;

    public function handle(): int
    {
        $appSettings = table::settings()
            ->where('id', 1)
            ->first();

        $appName = !empty($appSettings->app_name)
            ? $appSettings->app_name
            : 'Company';

        $today = Carbon::now()->startOfDay();

        $sent = 0;

        /*
        |--------------------------------------------------------------------------
        | Passport
        |--------------------------------------------------------------------------
        |
        | nationalid = passport number
        | idexpirydate = passport expiry date
        |
        */

        $people = table::people()
            ->where('employmentstatus', 'Active')
            ->whereNotNull('idexpirydate')
            ->get();

        foreach ($people as $person) {
            $sent += $this->checkAndSend(
                reference: $person->id,
                type: 'passport_expiry',
                label: 'Passport',
                documentNumber: $person->nationalid,
                expiryDate: $person->idexpirydate,
                email: $person->emailaddress,
                employeeName: mb_strtoupper(
                    $person->lastname . ', ' . $person->firstname
                ),
                appName: $appName,
                today: $today
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Visa
        |--------------------------------------------------------------------------
        */

        $companyRows = table::companydata()
            ->join(
                'tbl_people',
                'tbl_people.id',
                '=',
                'tbl_company_data.reference'
            )
            ->where('tbl_people.employmentstatus', 'Active')
            ->whereNotNull('tbl_company_data.visaend')
            ->get([
                'tbl_company_data.reference',
                'tbl_company_data.visaend',
                'tbl_people.firstname',
                'tbl_people.lastname',
                'tbl_people.emailaddress',
            ]);

        foreach ($companyRows as $row) {
            $sent += $this->checkAndSend(
                reference: $row->reference,
                type: 'visa_expiry',
                label: 'Visa',
                documentNumber: null,
                expiryDate: $row->visaend,
                email: $row->emailaddress,
                employeeName: mb_strtoupper(
                    $row->lastname . ', ' . $row->firstname
                ),
                appName: $appName,
                today: $today
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Share Code
        |--------------------------------------------------------------------------
        |
        | sharecode = actual share code
        | sharecode_expires_at = expiry date
        |
        */

        $shareCodePeople = table::people()
            ->where('employmentstatus', 'Active')
            ->whereNotNull('sharecode')
            ->whereNotNull('sharecode_expires_at')
            ->get();

        foreach ($shareCodePeople as $person) {
            $sent += $this->checkAndSend(
                reference: $person->id,
                type: 'sharecode_expiry',
                label: 'Share Code',
                documentNumber: $person->sharecode,
                expiryDate: $person->sharecode_expires_at,
                email: $person->emailaddress,
                employeeName: mb_strtoupper(
                    $person->lastname . ', ' . $person->firstname
                ),
                appName: $appName,
                today: $today
            );
        }

        $this->info(
            "Sent {$sent} expiry reminder email(s)."
        );

        return self::SUCCESS;
    }

    /**
     * Urgent day-based milestones.
     *
     * Passport and Visa:
     * - 20 days
     *
     * Share Code:
     * - 20 days
     * - 6 days
     */
    private function dayMilestonesFor(string $type): array
    {
        if ($type === 'sharecode_expiry') {
            return [6, 20];
        }

        return [20];
    }

    /**
     * Format remaining duration for display in the email.
     *
     * Example:
     * 2 months 15 days
     * 1 year 3 months 2 days
     */
    private function formatDuration(
        Carbon $today,
        Carbon $expiry
    ): string {
        $today = $today->copy()->startOfDay();
        $expiry = $expiry->copy()->startOfDay();

        $diff = $today->diff($expiry);

        $parts = [];

        if ($diff->y > 0) {
            $parts[] = $diff->y . ' ' .
                ($diff->y === 1 ? 'year' : 'years');
        }

        if ($diff->m > 0) {
            $parts[] = $diff->m . ' ' .
                ($diff->m === 1 ? 'month' : 'months');
        }

        if ($diff->d > 0 || empty($parts)) {
            $parts[] = $diff->d . ' ' .
                ($diff->d === 1 ? 'day' : 'days');
        }

        return implode(' ', $parts);
    }

    /**
     * Check expiry and send reminder.
     *
     * Daily reminders begin when 2 calendar months or less remain.
     */
    private function checkAndSend(
        int $reference,
        string $type,
        string $label,
        ?string $documentNumber,
        ?string $expiryDate,
        ?string $email,
        string $employeeName,
        string $appName,
        Carbon $today
    ): int {
        /*
        |--------------------------------------------------------------------------
        | Required data check
        |--------------------------------------------------------------------------
        */

        if (empty($expiryDate) || empty($email)) {
            return 0;
        }

        $expiry = Carbon::parse($expiryDate)->startOfDay();

        /*
        |--------------------------------------------------------------------------
        | Ignore already expired documents
        |--------------------------------------------------------------------------
        */

        if ($expiry->lt($today)) {
            return 0;
        }

        /*
        |--------------------------------------------------------------------------
        | Calculate remaining time
        |--------------------------------------------------------------------------
        */

        $daysRemaining = $today->diffInDays($expiry);

        /*
        |--------------------------------------------------------------------------
        | Check whether expiry is within 2 calendar months
        |--------------------------------------------------------------------------
        |
        | Using calendar months instead of only diffInMonths().
        |
        | Example:
        | Today: 24 August
        | Expiry: 24 October
        | Result: daily reminder starts.
        |
        */

        $dailyReminderStart = $expiry
            ->copy()
            ->subMonthsNoOverflow(self::DAILY_REMINDER_MONTHS);

        /*
        |--------------------------------------------------------------------------
        | Determine milestone
        |--------------------------------------------------------------------------
        |
        | Default:
        | - daily_2_months
        |
        | Urgent milestones override the daily milestone:
        | - 20_days
        | - 6_days for Share Code
        |
        */

        $milestone = null;

        // Send daily when 2 months or less remain.
        if ($today->gte($dailyReminderStart)) {
            $milestone = 'daily_2_months';
        }

        // Check urgent milestones.
        foreach ($this->dayMilestonesFor($type) as $dayThreshold) {
            if ($daysRemaining <= $dayThreshold) {
                $milestone = $dayThreshold . '_days';
                break;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Nothing to send yet
        |--------------------------------------------------------------------------
        */

        if ($milestone === null) {
            return 0;
        }

        /*
        |--------------------------------------------------------------------------
        | Prevent duplicate emails on the same day
        |--------------------------------------------------------------------------
        |
        | Same:
        | - employee
        | - document type
        | - milestone
        | - expiry date
        | - day
        |
        | Tomorrow it can send again.
        |
        */

        $alreadySentToday = DB::table('email_logs')
            ->where('reference', $reference)
            ->where('type', $type)
            ->where('milestone', $milestone)
            ->where(
                'document_date',
                $expiry->format('Y-m-d')
            )
            ->whereDate(
                'created_at',
                $today->format('Y-m-d')
            )
            ->where('status', 'sent')
            ->exists();

        if ($alreadySentToday) {
            return 0;
        }

        /*
        |--------------------------------------------------------------------------
        | Email details
        |--------------------------------------------------------------------------
        */

        $subject = "{$label} Expiry Reminder - {$appName}";

        $durationText = $this->formatDuration(
            $today,
            $expiry
        );

        /*
        |--------------------------------------------------------------------------
        | Send email
        |--------------------------------------------------------------------------
        */

        try {
            Mail::to($email)->send(
                new DocumentExpiryReminderMail(
                    $employeeName,
                    $label,
                    $documentNumber,
                    $expiry->format('d F Y'),
                    $durationText,
                    $appName
                )
            );

            /*
            |--------------------------------------------------------------------------
            | Log successful email
            |--------------------------------------------------------------------------
            */

            DB::table('email_logs')->insert([
                'reference' => $reference,
                'type' => $type,
                'milestone' => $milestone,
                'document_date' => $expiry->format('Y-m-d'),
                'to_email' => $email,
                'subject' => $subject,
                'sent_by' => null,
                'status' => 'sent',
                'error' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return 1;

        } catch (\Exception $e) {

            /*
            |--------------------------------------------------------------------------
            | Log error
            |--------------------------------------------------------------------------
            */

            \Log::error(
                "Failed to send {$type} reminder for reference {$reference}: "
                . $e->getMessage()
            );

            DB::table('email_logs')->insert([
                'reference' => $reference,
                'type' => $type,
                'milestone' => $milestone,
                'document_date' => $expiry->format('Y-m-d'),
                'to_email' => $email,
                'subject' => $subject,
                'sent_by' => null,
                'status' => 'failed',
                'error' => substr($e->getMessage(), 0, 500),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return 0;
        }
    }
}