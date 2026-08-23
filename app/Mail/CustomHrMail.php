<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CustomHrMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $employeeName;
    public string $emailSubject;
    public string $bodyText;
    public string $senderName;
    public string $appName;

    public function __construct(
        string $employeeName,
        string $subject,
        string $bodyText,
        string $senderName,
        string $appName
    ) {
        $this->employeeName = $employeeName;
        $this->emailSubject = $subject;
        $this->bodyText = $bodyText;
        $this->senderName = $senderName;
        $this->appName = $appName;
    }

    public function build()
    {
        return $this->from(config('mail.from.address'), $this->appName)
            ->subject($this->emailSubject)
            ->view('emails.custom-hr');
    }
}