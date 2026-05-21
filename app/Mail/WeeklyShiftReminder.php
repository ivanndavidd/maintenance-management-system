<?php

namespace App\Mail;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WeeklyShiftReminder extends Mailable
{
    use Queueable, SerializesModels;

    public Carbon $nextWeekStart;
    public Carbon $nextWeekEnd;
    public string $siteName;
    public bool $isReminder;

    public function __construct(Carbon $nextWeekStart, Carbon $nextWeekEnd, string $siteName, bool $isReminder = false)
    {
        $this->nextWeekStart = $nextWeekStart;
        $this->nextWeekEnd   = $nextWeekEnd;
        $this->siteName      = $siteName;
        $this->isReminder    = $isReminder;
    }

    public function envelope(): Envelope
    {
        $prefix = $this->isReminder ? '[Reminder] ' : '';
        return new Envelope(
            subject: $prefix . 'Shift Schedule Minggu Depan Belum Dibuat - ' . $this->siteName,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.shift.weekly-shift-reminder',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
