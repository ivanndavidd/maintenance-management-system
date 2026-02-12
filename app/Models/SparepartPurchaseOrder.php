<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SparepartPurchaseOrder extends TenantModels
{
    protected $fillable = [
        'po_number',
        'sparepart_id',
        'quantity_ordered',
        'unit_price',
        'total_price',
        'supplier',
        'order_date',
        'expected_delivery_date',
        'actual_delivery_date',
        'quantity_received',
        'status',
        'ordered_by',
        'received_by',
        'notes',
    ];

    protected $casts = [
        'order_date' => 'date',
        'expected_delivery_date' => 'date',
        'actual_delivery_date' => 'date',
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
    ];

    // Relationships
    public function sparepart()
    {
        return $this->belongsTo(Sparepart::class);
    }

    public function orderedByUser()
    {
        return $this->belongsTo(User::class, 'ordered_by');
    }

    public function receivedByUser()
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    // Generate PO number
    public static function generatePONumber()
    {
        $date = now();
        $prefix = 'PO';
        $dateFormat = $date->format('Ymd');

        $lastPO = self::whereDate('created_at', $date->toDateString())
            ->orderBy('id', 'desc')
            ->first();

        if ($lastPO) {
            $lastCode = $lastPO->po_number;
            $lastIncrement = (int) substr($lastCode, -3);
            $newIncrement = $lastIncrement + 1;
        } else {
            $newIncrement = 1;
        }

        return $prefix . '-' . $dateFormat . '-' . str_pad($newIncrement, 3, '0', STR_PAD_LEFT);
    }

    // Check if PO is fully received
    public function isFullyReceived()
    {
        return $this->quantity_received >= $this->quantity_ordered;
    }

    // Check if PO is partially received
    public function isPartiallyReceived()
    {
        return $this->quantity_received > 0 && $this->quantity_received < $this->quantity_ordered;
    }

    // Calculate remaining quantity
    public function getRemainingQuantity()
    {
        return $this->quantity_ordered - $this->quantity_received;
    }

    // Receive items
    public function receiveItems($quantity, $userId)
    {
        $this->quantity_received += $quantity;

        if ($this->quantity_received >= $this->quantity_ordered) {
            $this->status = 'received';
            $this->actual_delivery_date = now();
        } else {
            $this->status = 'partial_received';
        }

        $this->received_by = $userId;
        $this->save();

        // Update sparepart quantity
        $this->sparepart->quantity += $quantity;
        $this->sparepart->save();
    }
}
