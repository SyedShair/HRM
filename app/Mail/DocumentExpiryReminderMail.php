<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class DocumentExpiryReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $employeeName;
    public string $documentLabel; // 'Passport' or 'Visa'
    public ?string $documentNumber;
    public string $expiryDate;
    public int $daysRemaining;
    public string $appName;

    public function __construct(
        string $employeeName,
        string $documentLabel,
        ?string $documentNumber,
        string $expiryDate,
        int $daysRemaining,
        string $appName
    ) {
        $this->employeeName = $employeeName;
        $this->documentLabel = $documentLabel;
        $this->documentNumber = $documentNumber;
        $this->expiryDate = $expiryDate;
        $this->daysRemaining = $daysRemaining;
        $this->appName = $appName;
    }

    public function build()
    {
        return $this->from(config('mail.from.address'), $this->appName)
            ->subject("{$this->documentLabel} Expiry Reminder - {$this->appName}")
            ->view('emails.document-expiry-reminder');
    }
}