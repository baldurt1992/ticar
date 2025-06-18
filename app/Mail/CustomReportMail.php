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
    public $customAttachments;

    public function __construct(CustomReport $report, array $attachments = [])
    {
        $this->report = $report;
        $this->customAttachments = $attachments;

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

        if (is_array($this->customAttachments)) {
            foreach ($this->customAttachments as $attachment) {
                if (is_array($attachment) && isset($attachment['path']) && is_string($attachment['path'])) {
                    $mail->attach($attachment['path'], [
                        'as' => $attachment['name'] ?? basename($attachment['path']),
                        'mime' => $attachment['mime'] ?? null
                    ]);
                } elseif (is_string($attachment) && file_exists($attachment)) {
                    $mail->attach($attachment);
                }
            }
        } elseif (is_string($this->customAttachments) && file_exists($this->customAttachments)) {
            $mail->attach($this->customAttachments);
        }

        return $mail;
    }
}
