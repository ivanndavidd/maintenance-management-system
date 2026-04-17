<?php

namespace App\Mail;

use App\Models\Sparepart;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SparepartOutOfStock extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $sparepart;
    public $reportedBy;

    public function __construct(Sparepart $sparepart, User $reportedBy)
    {
        $this->sparepart = $sparepart;
        $this->reportedBy = $reportedBy;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '[Out of Stock] Sparepart: ' . $this->sparepart->sparepart_name,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.sparepart.out-of-stock',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
