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

    protected $description = 'Send automatic passport, visa, and share code expiry reminder emails at their configured milestones.';

    // Long-lead heads-up, shared by every document type. Matches the
    // colour-coding windows already used on the employee profile page.
    private const MONTHS_MILESTONE = 5;

    public function handle(): int
    {
        $appSettings = table::settings()->where('id', 1)->first();
        $appName = !empty($appSettings->app_name) ? $appSettings->app_name : 'Company';

        $today = Carbon::now()->startOfDay();
        $sent = 0;

        // ---- Passport ----
        // Confirmed field mapping: tbl_people.nationalid holds the
        // passport NUMBER (display-only, not used for the countdown);
        // tbl_people.idexpirydate is the passport's own expiry date and
        // is what drives this reminder. idissuedate (the passport's
        // issue date) is informational only and never used here.
        $people = table::people()
            ->where('employmentstatus', 'Active')
            ->whereNotNull('idexpirydate')
            ->get();

        foreach ($people as $person) {
            $sent += $this->checkAndSend(
                $person->id,
                'passport_expiry',
                'Passport',
                $person->nationalid,
                $person->idexpirydate,
                $person->emailaddress,
                mb_strtoupper($person->lastname.', '.$person->firstname),
                $appName,
                $today
            );
        }

        // ---- Visa ----
        $companyRows = table::companydata()
            ->join('tbl_people', 'tbl_people.id', '=', 'tbl_company_data.reference')
            ->where('tbl_people.employmentstatus', 'Active')
            ->whereNotNull('tbl_company_data.visaend')
            ->get([
                'tbl_company_data.reference', 'tbl_company_data.visaend',
                'tbl_people.firstname', 'tbl_people.lastname', 'tbl_people.emailaddress',
            ]);

        foreach ($companyRows as $row) {
            $sent += $this->checkAndSend(
                $row->reference,
                'visa_expiry',
                'Visa',
                null,
                $row->visaend,
                $row->emailaddress,
                mb_strtoupper($row->lastname.', '.$row->firstname),
                $appName,
                $today
            );
        }

        // ---- Share Code ----
        // tbl_people.sharecode is the code itself (display-only);
        // tbl_people.sharecode_expires_at drives the countdown. Optional
        // per employee (not everyone has one), so both must actually be
        // present on the record for this to fire at all.
        $shareCodePeople = table::people()
            ->where('employmentstatus', 'Active')
            ->whereNotNull('sharecode')
            ->whereNotNull('sharecode_expires_at')
            ->get();

        foreach ($shareCodePeople as $person) {
            $sent += $this->checkAndSend(
                $person->id,
                'sharecode_expiry',
                'Share Code',
                $person->sharecode,
                $person->sharecode_expires_at,
                $person->emailaddress,
                mb_strtoupper($person->lastname.', '.$person->firstname),
                $appName,
                $today
            );
        }

        $this->info("Sent {$sent} expiry reminder email(s).");

        return self::SUCCESS;
    }

    /**
     * Day-based milestones per document type, checked smallest-first so
     * the closest/most urgent matching threshold wins. Share codes get
     * an extra tight 6-day checkpoint on top of the standard 20-day one
     * - they tend to have much shorter validity windows than a passport
     * or visa, so a 20-day-out reminder alone isn't urgent enough right
     * before one actually lapses.
     *
     * @return int[] ascending day thresholds
     */
    private function dayMilestonesFor(string $type): array
    {
        if ($type === 'sharecode_expiry') {
            return [6, 20];
        }

        return [20];
    }

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
        if (empty($expiryDate) || empty($email)) {
            return 0;
        }

        $expiry = Carbon::parse($expiryDate)->startOfDay();

        // Already expired - not this command's concern; the person
        // should already have had reminders on the way in, and an
        // "already expired" notice is a different kind of message.
        if ($expiry->lt($today)) {
            return 0;
        }

        $daysRemaining = $today->diffInDays($expiry);
        $monthsRemaining = $today->diffInMonths($expiry);

        // Check the tightest (nearest-to-expiry) day threshold first,
        // so e.g. a share code within 6 days gets its own '6_days'
        // milestone rather than only ever re-matching '20_days' (which
        // would already have been sent and logged earlier, so it'd be
        // silently skipped as a duplicate below).
        $milestone = null;
        foreach ($this->dayMilestonesFor($type) as $dayThreshold) {
            if ($daysRemaining <= $dayThreshold) {
                $milestone = $dayThreshold.'_days';
                break;
            }
        }

        if ($milestone === null && $monthsRemaining <= self::MONTHS_MILESTONE) {
            $milestone = self::MONTHS_MILESTONE.'_months';
        }

        if ($milestone === null) {
            return 0;
        }

        // Already sent this exact milestone for this exact expiry date?
        // Keyed on document_date too, so a renewed document (new expiry
        // date) starts a fresh reminder cycle instead of being silenced
        // forever by an old log row.
        $alreadySent = DB::table('email_logs')
            ->where('reference', $reference)
            ->where('type', $type)
            ->where('milestone', $milestone)
            ->where('document_date', $expiry->format('Y-m-d'))
            ->exists();

        if ($alreadySent) {
            return 0;
        }

        $subject = "{$label} Expiry Reminder - {$appName}";

        try {
            Mail::to($email)->send(new DocumentExpiryReminderMail(
                $employeeName,
                $label,
                $documentNumber,
                $expiry->format('d F Y'),
                $daysRemaining,
                $appName
            ));

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
            \Log::error("Failed to send {$type} reminder for reference {$reference}: ".$e->getMessage());

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