<?php

namespace App\Mail;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class MissingShiftScheduleAlert extends Mailable
{
    use Queueable, SerializesModels;

    public $date;
    public $unassignedTasks;
    public $siteName;

    public function __construct(Carbon $date, Collection $unassignedTasks, string $siteName)
    {
        $this->date = $date;
        $this->unassignedTasks = $unassignedTasks;
        $this->siteName = $siteName;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '[Alert] No Shift Schedule for ' . $this->date->format('d M Y') . ' - ' . $this->unassignedTasks->count() . ' PM Tasks Unassigned',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.pm.missing-shift-schedule-alert',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
