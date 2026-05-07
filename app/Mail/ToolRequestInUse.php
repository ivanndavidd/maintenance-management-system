<?php

namespace App\Mail;

use App\Models\ToolUsageRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ToolRequestInUse extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public ToolUsageRequest $toolRequest) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '[In Use] Tool Usage Request ' . $this->toolRequest->request_number,
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.tool-request.in-use');
    }

    public function attachments(): array { return []; }
}
