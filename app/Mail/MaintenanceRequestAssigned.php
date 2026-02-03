<?php

namespace App\Mail;

use App\Models\CorrectiveMaintenanceRequest;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MaintenanceRequestAssigned extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $ticket;
    public $technician;
    public $shiftInfo;

    /**
     * Create a new message instance.
     */
    public function __construct(CorrectiveMaintenanceRequest $ticket, ?User $technician = null, ?string $shiftInfo = null)
    {
        $this->ticket = $ticket;
        $this->technician = $technician ?? $ticket->assignedUser;
        $this->shiftInfo = $shiftInfo;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '[New Assignment] ' . $this->ticket->ticket_number . ' - Maintenance Request',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.maintenance.assigned',
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
