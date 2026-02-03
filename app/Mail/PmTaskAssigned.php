<?php

namespace App\Mail;

use App\Models\PmTask;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PmTaskAssigned extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $task;

    /**
     * Create a new message instance.
     */
    public function __construct(PmTask $task)
    {
        $this->task = $task;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '[PM Task Assigned] ' . $this->task->task_name,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.pm.task-assigned',
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
