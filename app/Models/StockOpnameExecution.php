<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class StockOpnameExecution extends Model
{
    protected $fillable = [
        'execution_code',
        'schedule_id',
        'item_type',
        'item_id',
        'execution_date',
        'scheduled_date',
        'system_quantity',
        'physical_quantity',
        'discrepancy_qty',
        'discrepancy_value',
        'status',
        'is_missed',
        'days_difference',
        'executed_by',
        'verified_by',
        'verified_at',
        'notes',
        'discrepancy_notes',
    ];

    protected $casts = [
        'execution_date' => 'date',
        'scheduled_date' => 'date',
        'verified_at' => 'datetime',
        'discrepancy_value' => 'decimal:2',
        'is_missed' => 'boolean',
    ];

    // Relationships
    public function schedule()
    {
        return $this->belongsTo(StockOpnameSchedule::class, 'schedule_id');
    }

    public function executedByUser()
    {
        return $this->belongsTo(User::class, 'executed_by');
    }

    public function verifiedByUser()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function item()
    {
        if ($this->item_type === 'sparepart') {
            return $this->belongsTo(Sparepart::class, 'item_id');
        } else {
            return $this->belongsTo(Tool::class, 'item_id');
        }
    }

    public function adjustments()
    {
        return $this->hasMany(StockAdjustment::class, 'related_opname_execution_id');
    }

    // Generate execution code with fixed logic
    public static function generateExecutionCode()
    {
        $date = now();
        $prefix = 'SOE';
        $dateFormat = $date->format('Ymd');

        // Get last execution with execution_code that matches today's date pattern
        $pattern = $prefix . '-' . $dateFormat . '-%';
        $lastExecution = self::where('execution_code', 'LIKE', $pattern)
            ->orderBy('execution_code', 'desc')
            ->first();

        if ($lastExecution && $lastExecution->execution_code) {
            $lastCode = $lastExecution->execution_code;
            $lastIncrement = (int) substr($lastCode, -3);
            $newIncrement = $lastIncrement + 1;
        } else {
            $newIncrement = 1;
        }

        return $prefix . '-' . $dateFormat . '-' . str_pad($newIncrement, 3, '0', STR_PAD_LEFT);
    }

    // Helper to get item name
    public function getItemName()
    {
        if ($this->item_type === 'sparepart') {
            $sparepart = Sparepart::find($this->item_id);
            return $sparepart ? $sparepart->sparepart_name : 'Unknown Sparepart';
        } elseif ($this->item_type === 'tool') {
            $tool = Tool::find($this->item_id);
            return $tool ? $tool->sparepart_name : 'Unknown Tool';
        }

        return 'Unknown Item';
    }

    // Helper to get item code
    public function getItemCode()
    {
        if ($this->item_type === 'sparepart') {
            $sparepart = Sparepart::find($this->item_id);
            return $sparepart ? ($sparepart->sparepart_id ?? '-') : '-';
        } elseif ($this->item_type === 'tool') {
            $tool = Tool::find($this->item_id);
            return $tool ? ($tool->tool_id ?? '-') : '-';
        }

        return '-';
    }

    // Calculate discrepancy
    public function calculateDiscrepancy($itemPrice)
    {
        $this->discrepancy_qty = $this->physical_quantity - $this->system_quantity;
        $this->discrepancy_value = $this->discrepancy_qty * $itemPrice;
        $this->save();
    }

    // Calculate compliance status
    public function calculateComplianceStatus()
    {
        if (!$this->scheduled_date) {
            $this->status = 'on_time';
            $this->is_missed = false;
            $this->days_difference = 0;
            $this->save();
            return;
        }

        $executionDate = Carbon::parse($this->execution_date);
        $scheduledDate = Carbon::parse($this->scheduled_date);
        $daysDiff = $executionDate->diffInDays($scheduledDate, false);

        $this->days_difference = abs($daysDiff);

        if ($daysDiff > 0) {
            // Execution before scheduled date
            $this->status = 'early';
            $this->is_missed = false;
        } elseif ($daysDiff < -7) {
            // More than 7 days late
            $this->status = 'late';
            $this->is_missed = true;
        } elseif ($daysDiff < 0) {
            // Late but within 7 days
            $this->status = 'late';
            $this->is_missed = false;
        } else {
            // On time
            $this->status = 'on_time';
            $this->is_missed = false;
        }

        $this->save();
    }

    // Check if has discrepancy
    public function hasDiscrepancy()
    {
        return $this->discrepancy_qty != 0;
    }

    // Get accuracy percentage
    public function getAccuracyPercentage()
    {
        if ($this->system_quantity == 0) {
            return 100;
        }

        $accuracy = (1 - abs($this->discrepancy_qty) / $this->system_quantity) * 100;
        return max(0, min(100, $accuracy));
    }
}
