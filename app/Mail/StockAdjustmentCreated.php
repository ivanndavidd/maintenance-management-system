<?php

namespace App\Mail;

use App\Models\StockAdjustment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class StockAdjustmentCreated extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $adjustment;
    public $itemName;

    /**
     * Create a new message instance.
     */
    public function __construct(StockAdjustment $adjustment)
    {
        // Load relationships
        $adjustment->load('adjustedByUser', 'approvedByUser');

        // Pre-compute item name before serialization
        $this->itemName = $adjustment->getItemName();

        $this->adjustment = $adjustment;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '[Stock Adjustment] ' . $this->adjustment->adjustment_code . ' - ' . ucfirst($this->adjustment->reason_category),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.stock-adjustment.created',
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
