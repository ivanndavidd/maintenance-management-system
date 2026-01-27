<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tool extends Model
{
    protected $fillable = [
        'tool_id',
        'equipment_type',
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

    // Generate tool ID
    public static function generateToolId()
    {
        $date = now();
        $prefix = 'TLS';
        $dateFormat = $date->format('Ymd');

        // Get last tool with tool_id that matches today's date pattern
        $pattern = $prefix . $dateFormat . '%';
        $lastTool = self::where('tool_id', 'LIKE', $pattern)
            ->orderBy('tool_id', 'desc')
            ->first();

        if ($lastTool && $lastTool->tool_id) {
            // Extract the increment number from the last tool_id
            $lastCode = $lastTool->tool_id;
            $lastIncrement = (int) substr($lastCode, -3);
            $newIncrement = $lastIncrement + 1;
        } else {
            $newIncrement = 1;
        }

        // Format: TLS + YYYYMMDD + 001
        return $prefix . $dateFormat . str_pad($newIncrement, 3, '0', STR_PAD_LEFT);
    }

    // Generate material code (same as tool_id for consistency)
    public static function generateMaterialCode()
    {
        return self::generateToolId();
    }

    // Boot method to auto-generate codes if not provided
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($tool) {
            if (empty($tool->tool_id)) {
                $tool->tool_id = self::generateToolId();
            }

            if (empty($tool->material_code)) {
                $tool->material_code = $tool->tool_id;
            }
        });
    }
}
