<?php

namespace App\Mail;

use App\Models\PmTaskReport;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PmSparepartRejected extends Mailable
{
    use Queueable, SerializesModels;

    public PmTaskReport $report;
    public string $reason;

    public function __construct(PmTaskReport $report, string $reason)
    {
        $this->report = $report;
        $this->reason = $reason;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '[PM Report] Penggunaan Sparepart Ditolak — ' . $this->report->task->task_name,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.pm.sparepart-rejected',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
