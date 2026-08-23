<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class RotaScheduleMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $employeeName;
    public string $datefrom;
    public string $dateto;
    public string $weeklyHours;
    public $shifts; // Collection keyed by day name
    public array $days;
    public string $appName;

    public function __construct(
        string $employeeName,
        string $datefrom,
        string $dateto,
        string $weeklyHours,
        $shifts,
        array $days,
        string $appName
    ) {
        $this->employeeName = $employeeName;
        $this->datefrom = $datefrom;
        $this->dateto = $dateto;
        $this->weeklyHours = $weeklyHours;
        $this->shifts = $shifts;
        $this->days = $days;
        $this->appName = $appName;
    }

    public function build()
    {
        return $this->from(config('mail.from.address'), $this->appName)
            ->subject("Your Weekly Schedule - {$this->appName}")
            ->view('emails.rota-schedule');
    }
}