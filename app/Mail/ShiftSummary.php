<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ShiftSummary extends Mailable
{
    use Queueable, SerializesModels;

    public array $payload;
    public User $recipient;

    public function __construct(array $payload, User $recipient)
    {
        $this->payload   = $payload;
        $this->recipient = $recipient;
    }

    public function envelope(): Envelope
    {
        $date  = $this->payload['taskDate']->format('d M Y');
        $shift = $this->payload['shiftLabel'];

        return new Envelope(
            subject: "[Shift Summary] {$shift} — {$date} | {$this->payload['siteName']}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.shift.summary',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
