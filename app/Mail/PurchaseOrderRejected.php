<?php

namespace App\Mail;

use App\Models\PurchaseOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PurchaseOrderRejected extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $purchaseOrder;

    public function __construct(PurchaseOrder $purchaseOrder)
    {
        $this->purchaseOrder = $purchaseOrder;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '[Rejected] Purchase Order ' . $this->purchaseOrder->po_number,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.purchase-order.rejected',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
