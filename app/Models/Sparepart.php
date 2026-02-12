<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sparepart extends TenantModels
{
    protected $fillable = [
        'equipment_type',
        'sparepart_id',
        'material_code',
        'sparepart_name',
        'brand',
        'model',
        'quantity',
        'unit',
        'minimum_stock',
        'vulnerability',
        'location',
        'parts_price',
        'item_type',
        'path',
        'physical_quantity',
        'discrepancy_qty',
        'discrepancy_value',
        'opname_status',
        'opname_date',
        'opname_by',
        'verified_by',
        'adjustment_qty',
        'adjustment_reason',
        'last_opname_at',
        'add_part_by',
    ];

    protected $casts = [
        'opname_date' => 'date',
        'last_opname_at' => 'datetime',
        'parts_price' => 'decimal:2',
        'discrepancy_value' => 'decimal:2',
    ];

    // Relationships
    public function opnameUser()
    {
        return $this->belongsTo(User::class, 'opname_by');
    }

    public function verifiedUser()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function addedByUser()
    {
        return $this->belongsTo(User::class, 'add_part_by');
    }

    public function purchaseOrders()
    {
        return $this->hasMany(SparepartPurchaseOrder::class, 'sparepart_id');
    }

    public function opnameExecutions()
    {
        return $this->morphMany(StockOpnameExecution::class, 'item');
    }

    public function adjustments()
    {
        return $this->morphMany(StockAdjustment::class, 'item');
    }

    // Check if stock is low
    public function isLowStock()
    {
        return $this->quantity <= $this->minimum_stock && $this->quantity > 0;
    }

    // Check if out of stock
    public function isOutOfStock()
    {
        return $this->quantity == 0;
    }

    // Calculate discrepancy
    public function calculateDiscrepancy()
    {
        if ($this->physical_quantity !== null) {
            $this->discrepancy_qty = $this->physical_quantity - $this->quantity;
            $this->discrepancy_value = $this->discrepancy_qty * $this->parts_price;
            $this->save();
        }
    }

    // Generate material code
    public static function generateSparepartId()
    {
        $date = now();
        $prefix = 'SPR';
        $dateFormat = $date->format('Ymd'); // 20250611

        // Get last sparepart created today
        $lastSparepart = self::whereDate('created_at', $date->toDateString())
            ->orderBy('id', 'desc')
            ->first();

        if ($lastSparepart) {
            // Extract the increment number from the last sparepart_id
            $lastCode = $lastSparepart->sparepart_id;
            $lastIncrement = (int) substr($lastCode, -3);
            $newIncrement = $lastIncrement + 1;
        } else {
            $newIncrement = 1;
        }

        // Format: SPR + YYYYMMDD + 001
        return $prefix . $dateFormat . str_pad($newIncrement, 3, '0', STR_PAD_LEFT);
    }
}
