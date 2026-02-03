<?php

namespace App\Mail;

use App\Models\StockOpnameSchedule;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class StockOpnameItemCompleted extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $schedule;
    public $itemsWithDiscrepancy;

    /**
     * Create a new message instance.
     */
    public function __construct(StockOpnameSchedule $schedule, array $itemsWithDiscrepancy)
    {
        $this->schedule = $schedule;
        $this->itemsWithDiscrepancy = $itemsWithDiscrepancy;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '[Stock Opname Review Required] ' . $this->schedule->schedule_code . ' - Items with Discrepancies',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.stock-opname.item-completed',
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
