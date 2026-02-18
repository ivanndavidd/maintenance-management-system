<?php

namespace App\Mail;

use App\Models\PmTask;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PmTaskDueReminder extends Mailable
{
    use Queueable, SerializesModels;

    public $task;

    public function __construct(PmTask $task)
    {
        $this->task = $task;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '[PM Task Due Today] ' . $this->task->task_name,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.pm.task-due-reminder',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
