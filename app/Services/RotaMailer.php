<?php

namespace App\Services;

use App\Classes\table;
use App\Mail\RotaScheduleMail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class RotaMailer
{
    private const DAYS = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

    /**
     * Email one employee their weekly schedule and log the attempt.
     * Never throws - a failed/skipped send should never block the
     * schedule save itself when called automatically from
     * SchedulesController::add()/update().
     *
     * @param int $scheduleId tbl_people_schedules.id
     * @param int|null $sentBy users.id if triggered by a manual button, null if automatic
     * @return array{sent: bool, reason: string|null} reason is set when sent is false
     */
    public static function send(int $scheduleId, ?int $sentBy = null): array
    {
        $schedule = table::schedules()->where('id', $scheduleId)->first();
        if (!$schedule) {
            return ['sent' => false, 'reason' => 'Schedule not found.'];
        }

        $employee = table::people()->where('id', $schedule->reference)->first();
        if (!$employee) {
            return ['sent' => false, 'reason' => 'Employee not found.'];
        }

        $companyData = table::companydata()->where('reference', $schedule->reference)->first();
        $toEmail = $employee->emailaddress ?: ($companyData->companyemail ?? null);

        if (!$toEmail) {
            return ['sent' => false, 'reason' => 'No email address on file for this employee.'];
        }

        $shifts = table::weeklyshifts()->where('schedual_id', $scheduleId)->get()->keyBy('day');

        $appSettings = table::settings()->where('id', 1)->first();
        $appName = !empty($appSettings->app_name) ? $appSettings->app_name : 'Company';

        $employeeName = mb_strtoupper($employee->lastname.', '.$employee->firstname);
        $subject = "Your Weekly Schedule - {$appName}";

        try {
            Mail::to($toEmail)->send(new RotaScheduleMail(
                $employeeName,
                date('M d, Y', strtotime($schedule->datefrom)),
                date('M d, Y', strtotime($schedule->dateto)),
                (string) $schedule->hours,
                $shifts,
                self::DAYS,
                $appName
            ));

            DB::table('email_logs')->insert([
                'reference' => $employee->id,
                'type' => 'rota_notification',
                'milestone' => null,
                'document_date' => null,
                'to_email' => $toEmail,
                'subject' => $subject,
                'sent_by' => $sentBy,
                'status' => 'sent',
                'error' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return ['sent' => true, 'reason' => null];
        } catch (\Exception $e) {
            \Log::error('Failed to email rota schedule #'.$scheduleId.': '.$e->getMessage());

            DB::table('email_logs')->insert([
                'reference' => $employee->id,
                'type' => 'rota_notification',
                'milestone' => null,
                'document_date' => null,
                'to_email' => $toEmail,
                'subject' => $subject,
                'sent_by' => $sentBy,
                'status' => 'failed',
                'error' => substr($e->getMessage(), 0, 500),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return ['sent' => false, 'reason' => 'The email failed to send. Please try again.'];
        }
    }
}