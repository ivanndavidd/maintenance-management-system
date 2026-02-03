<?php

namespace App\Mail;

use App\Models\StockOpnameSchedule;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class StockOpnameAssigned extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $schedule;
    public $user;

    /**
     * Create a new message instance.
     */
    public function __construct(StockOpnameSchedule $schedule, User $user)
    {
        $this->schedule = $schedule;
        $this->user = $user;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '[Stock Opname Assigned] ' . $this->schedule->schedule_code,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.stock-opname.assigned',
        );
    }

    /**
     * Get the attachments for the message.
     */
    public function attachments(): array
    {
        return [];
    }
}
