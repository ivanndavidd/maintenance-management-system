<?php

namespace App\Mail;

use App\Models\CmReport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SparepartUsedInCm extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $report;
    public $usages;
    public $ticketNumber;
    public $submitterName;

    public function __construct(CmReport $report, array $usages)
    {
        $this->report = $report;
        $this->usages = $usages;
        $this->ticketNumber = $report->cmRequest->ticket_number ?? '-';
        $this->submitterName = $report->submitter->name ?? '-';
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '[Sparepart Used] CM Ticket ' . $this->ticketNumber,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.sparepart.used-in-cm',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
