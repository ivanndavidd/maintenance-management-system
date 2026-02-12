<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockAdjustment extends TenantModels
{
    protected $fillable = [
        'adjustment_code',
        'item_type',
        'item_id',
        'quantity_before',
        'quantity_after',
        'adjustment_qty',
        'adjustment_type',
        'reason_category',
        'reason',
        'value_impact',
        'adjusted_by',
        'approved_by',
        'approved_at',
        'status',
        'approval_notes',
        'related_opname_execution_id',
        'related_opname_item_id',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
        'value_impact' => 'decimal:2',
    ];

    // Relationships
    public function adjustedByUser()
    {
        return $this->belongsTo(User::class, 'adjusted_by');
    }

    public function approvedByUser()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function relatedOpnameExecution()
    {
        return $this->belongsTo(StockOpnameExecution::class, 'related_opname_execution_id');
    }

    public function relatedOpnameItem()
    {
        return $this->belongsTo(StockOpnameScheduleItem::class, 'related_opname_item_id');
    }

    public function item()
    {
        if ($this->item_type === 'sparepart') {
            return $this->belongsTo(Sparepart::class, 'item_id');
        } elseif ($this->item_type === 'tool') {
            return $this->belongsTo(Tool::class, 'item_id');
        } elseif ($this->item_type === 'asset') {
            return $this->belongsTo(Asset::class, 'item_id');
        }
        return null;
    }

    // Generate adjustment code
    public static function generateAdjustmentCode()
    {
        $date = now();
        $prefix = 'ADJ';
        $dateFormat = $date->format('Ymd');

        $lastAdjustment = self::whereDate('created_at', $date->toDateString())
            ->orderBy('id', 'desc')
            ->first();

        if ($lastAdjustment) {
            $lastCode = $lastAdjustment->adjustment_code;
            $lastIncrement = (int) substr($lastCode, -3);
            $newIncrement = $lastIncrement + 1;
        } else {
            $newIncrement = 1;
        }

        return $prefix . $dateFormat . str_pad($newIncrement, 3, '0', STR_PAD_LEFT);
    }

    // Apply adjustment to item
    public function applyAdjustment()
    {
        if ($this->status !== 'approved') {
            return false;
        }

        if ($this->item_type === 'sparepart') {
            $item = Sparepart::find($this->item_id);
        } elseif ($this->item_type === 'tool') {
            $item = Tool::find($this->item_id);
        } elseif ($this->item_type === 'asset') {
            $item = Asset::find($this->item_id);
        } else {
            return false;
        }

        if (!$item) {
            return false;
        }

        $item->quantity = $this->quantity_after;
        $item->adjustment_qty = $this->adjustment_qty;
        $item->adjustment_reason = $this->reason;
        $item->save();

        return true;
    }

    // Create adjustment from approved opname item
    public static function createFromOpnameItem(StockOpnameScheduleItem $opnameItem)
    {
        // Validate item is approved and has discrepancy
        if ($opnameItem->review_status !== 'approved' || !$opnameItem->hasDiscrepancy()) {
            return null;
        }

        // Check if adjustment already exists for this item
        $existingAdjustment = self::where('related_opname_item_id', $opnameItem->id)->first();
        if ($existingAdjustment) {
            return $existingAdjustment;
        }

        // Get item price for value impact calculation
        $itemPrice = 0;
        if ($opnameItem->item_type === 'sparepart') {
            $item = Sparepart::find($opnameItem->item_id);
            $itemPrice = $item ? $item->price : 0;
        } elseif ($opnameItem->item_type === 'tool') {
            $item = Tool::find($opnameItem->item_id);
            $itemPrice = $item ? $item->price : 0;
        } elseif ($opnameItem->item_type === 'asset') {
            $item = Asset::find($opnameItem->item_id);
            $itemPrice = $item ? $item->purchase_price : 0;
        }

        // Create adjustment record
        $adjustment = self::create([
            'adjustment_code' => self::generateAdjustmentCode(),
            'item_type' => $opnameItem->item_type,
            'item_id' => $opnameItem->item_id,
            'quantity_before' => $opnameItem->system_quantity,
            'quantity_after' => $opnameItem->physical_quantity,
            'adjustment_qty' => $opnameItem->discrepancy_qty,
            'adjustment_type' => 'correction',
            'reason_category' => 'opname_result',
            'reason' =>
                'Stock Opname Result - Schedule: ' .
                $opnameItem->schedule->schedule_code .
                ($opnameItem->notes ? ' | Notes: ' . $opnameItem->notes : '') .
                ($opnameItem->review_notes ? ' | Review: ' . $opnameItem->review_notes : ''),
            'value_impact' => $opnameItem->discrepancy_value,
            'adjusted_by' => $opnameItem->executed_by,
            'approved_by' => $opnameItem->reviewed_by,
            'approved_at' => $opnameItem->reviewed_at,
            'status' => 'approved',
            'related_opname_item_id' => $opnameItem->id,
        ]);

        return $adjustment;
    }

    // Calculate value impact
    public function calculateValueImpact($itemPrice)
    {
        $this->value_impact = $this->adjustment_qty * $itemPrice;
        $this->save();
    }

    // Get item name
    public function getItemName()
    {
        if ($this->item_type === 'sparepart') {
            $item = Sparepart::find($this->item_id);
            return $item ? $item->sparepart_name : 'Unknown Sparepart';
        } elseif ($this->item_type === 'tool') {
            $item = Tool::find($this->item_id);
            return $item ? $item->sparepart_name : 'Unknown Tool';
        } elseif ($this->item_type === 'asset') {
            $item = Asset::find($this->item_id);
            return $item ? $item->asset_name : 'Unknown Asset';
        }
        return 'Unknown Item';
    }
}
