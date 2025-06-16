<?php

namespace App\Mail;

use App\Models\CustomReport;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CustomReportMail extends Mailable
{
    use Queueable, SerializesModels;

    public $report;
    public $attachments;

    public function __construct(CustomReport $report, array $attachments = [])
    {
        $this->report = $report;
        $this->attachments = $attachments;

        // Casting seguro por si vienen como string JSON
        $this->report->filters = is_array($this->report->filters)
            ? $this->report->filters
            : json_decode($this->report->filters, true) ?? [];

        $this->report->columns = is_array($this->report->columns)
            ? $this->report->columns
            : json_decode($this->report->columns, true) ?? [];

        $this->report->emails = is_array($this->report->emails)
            ? $this->report->emails
            : json_decode($this->report->emails, true) ?? [];
    }

    public function build()
    {
        $mail = $this->subject('Reporte automático: ' . ($this->report->name ?? ''))
            ->view('emails.custom_report', ['report' => $this->report]);

        foreach ($this->attachments as $filePath) {
            if (file_exists($filePath)) {
                $mail->attach($filePath);
            }
        }

        return $mail;
    }
}
