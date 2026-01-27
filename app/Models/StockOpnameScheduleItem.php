<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockOpnameScheduleItem extends Model
{
    protected $fillable = [
        'schedule_id',
        'item_type',
        'item_id',
        'execution_status',
        'review_status',
        'executed_by',
        'reviewed_by',
        'system_quantity',
        'physical_quantity',
        'discrepancy_qty',
        'discrepancy_value',
        'notes',
        'review_notes',
        'executed_at',
        'reviewed_at',
        'is_active',
    ];

    protected $casts = [
        'executed_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'discrepancy_value' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    // Relationship to schedule
    public function schedule()
    {
        return $this->belongsTo(StockOpnameSchedule::class, 'schedule_id');
    }

    // Relationship to user who executed the opname
    public function executedByUser()
    {
        return $this->belongsTo(User::class, 'executed_by');
    }

    // Relationship to user who reviewed the item
    public function reviewedByUser()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    // Polymorphic relationship to item (Sparepart, Tool, or Asset)
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

    // Get sparepart (if item_type is sparepart)
    public function sparepart()
    {
        return $this->belongsTo(Sparepart::class, 'item_id');
    }

    // Get tool (if item_type is tool)
    public function tool()
    {
        return $this->belongsTo(Tool::class, 'item_id');
    }

    // Get asset (if item_type is asset)
    public function asset()
    {
        return $this->belongsTo(Asset::class, 'item_id');
    }

    // Helper method to get item name
    public function getItemName()
    {
        if ($this->item_type === 'sparepart') {
            $sparepart = Sparepart::find($this->item_id);
            return $sparepart ? $sparepart->sparepart_name : 'N/A';
        } elseif ($this->item_type === 'tool') {
            $tool = Tool::find($this->item_id);
            return $tool ? $tool->sparepart_name : 'N/A';
        } elseif ($this->item_type === 'asset') {
            $asset = Asset::find($this->item_id);
            return $asset ? $asset->asset_name : 'N/A';
        }

        return 'N/A';
    }

    // Helper method to get item code
    public function getItemCode()
    {
        if ($this->item_type === 'sparepart') {
            $sparepart = Sparepart::find($this->item_id);
            return $sparepart ? ($sparepart->material_code ?? 'N/A') : 'N/A';
        } elseif ($this->item_type === 'tool') {
            $tool = Tool::find($this->item_id);
            return $tool ? ($tool->material_code ?? 'N/A') : 'N/A';
        } elseif ($this->item_type === 'asset') {
            $asset = Asset::find($this->item_id);
            return $asset ? ($asset->equipment_id ?? 'N/A') : 'N/A';
        }

        return 'N/A';
    }

    // Helper method to get item location
    public function getItemLocation()
    {
        if ($this->item_type === 'sparepart') {
            $sparepart = Sparepart::find($this->item_id);
            return $sparepart ? ($sparepart->location ?? '-') : '-';
        } elseif ($this->item_type === 'tool') {
            return 'Tool Storage'; // Tools don't have specific locations
        } elseif ($this->item_type === 'asset') {
            $asset = Asset::find($this->item_id);
            return $asset ? ($asset->location ?? '-') : '-';
        }

        return '-';
    }

    // Mark item as in progress
    public function markInProgress($userId)
    {
        $this->execution_status = 'in_progress';
        $this->executed_by = $userId;
        $this->save();
    }

    // Mark item as completed and calculate discrepancy
    public function markCompleted($userId, $physicalQuantity, $notes = null)
    {
        // Get current system quantity from the item
        $systemQty = $this->getCurrentSystemQuantity();

        $this->execution_status = 'completed';
        $this->executed_by = $userId;
        $this->system_quantity = $systemQty;
        $this->physical_quantity = $physicalQuantity;
        $this->discrepancy_qty = $physicalQuantity - $systemQty;

        // Calculate discrepancy value based on item price
        $itemPrice = $this->getItemPrice();
        $this->discrepancy_value = ($physicalQuantity - $systemQty) * $itemPrice;

        $this->notes = $notes;
        $this->executed_at = now();

        // Set review status based on discrepancy
        if ($this->discrepancy_qty != 0) {
            $this->review_status = 'pending_review';
        } else {
            $this->review_status = 'no_review_needed';
        }

        $this->save();

        // Update schedule completed count
        $this->schedule->updateCompletedCount();
    }

    // Mark item as cancelled
    public function markCancelled($notes = null)
    {
        $this->execution_status = 'cancelled';
        $this->notes = $notes;
        $this->save();

        // Update schedule cancelled count
        $this->schedule->updateCancelledCount();
    }

    // Get current system quantity from the item
    private function getCurrentSystemQuantity()
    {
        if ($this->item_type === 'sparepart') {
            $sparepart = Sparepart::find($this->item_id);
            return $sparepart ? $sparepart->quantity : 0;
        } elseif ($this->item_type === 'tool') {
            $tool = Tool::find($this->item_id);
            return $tool ? $tool->quantity : 0;
        } elseif ($this->item_type === 'asset') {
            // Assets typically have quantity of 1
            return 1;
        }

        return 0;
    }

    // Get item price for discrepancy calculation
    private function getItemPrice()
    {
        if ($this->item_type === 'sparepart') {
            $sparepart = Sparepart::find($this->item_id);
            return $sparepart ? $sparepart->price : 0;
        } elseif ($this->item_type === 'tool') {
            $tool = Tool::find($this->item_id);
            return $tool ? $tool->price : 0;
        } elseif ($this->item_type === 'asset') {
            $asset = Asset::find($this->item_id);
            return $asset ? $asset->purchase_price : 0;
        }

        return 0;
    }

    // Check if item is pending
    public function isPending()
    {
        return $this->execution_status === 'pending';
    }

    // Check if item is in progress
    public function isInProgress()
    {
        return $this->execution_status === 'in_progress';
    }

    // Check if item is completed
    public function isCompleted()
    {
        return $this->execution_status === 'completed';
    }

    // Check if item has discrepancy
    public function hasDiscrepancy()
    {
        return $this->discrepancy_qty != 0;
    }

    // Admin review methods
    public function needsReview()
    {
        return $this->review_status === 'pending_review';
    }

    public function isReviewed()
    {
        return in_array($this->review_status, ['approved', 'rejected']);
    }

    public function isApproved()
    {
        return $this->review_status === 'approved';
    }

    public function isRejected()
    {
        return $this->review_status === 'rejected';
    }

    // Approve discrepancy
    public function approveDiscrepancy($adminId, $reviewNotes = null)
    {
        $this->review_status = 'approved';
        $this->reviewed_by = $adminId;
        $this->reviewed_at = now();
        $this->review_notes = $reviewNotes;
        $this->save();
    }

    // Reject discrepancy (requires re-execution)
    public function rejectDiscrepancy($adminId, $reviewNotes)
    {
        $this->review_status = 'rejected';
        $this->reviewed_by = $adminId;
        $this->reviewed_at = now();
        $this->review_notes = $reviewNotes;

        // Reset execution data to allow re-execution
        $this->execution_status = 'pending';
        $this->executed_by = null;
        $this->system_quantity = null;
        $this->physical_quantity = null;
        $this->discrepancy_qty = 0;
        $this->discrepancy_value = 0;
        $this->notes = null;
        $this->executed_at = null;

        $this->save();

        // Update schedule counts
        $this->schedule->updateCompletedCount();
        $this->schedule->updateCancelledCount();
    }

    // Get review status badge color
    public function getReviewStatusBadgeColor()
    {
        return match($this->review_status) {
            'pending_review' => 'warning',
            'approved' => 'success',
            'rejected' => 'danger',
            'no_review_needed' => 'secondary',
            default => 'secondary',
        };
    }

    // Get review status label
    public function getReviewStatusLabel()
    {
        return match($this->review_status) {
            'pending_review' => 'Pending Review',
            'approved' => 'Approved',
            'rejected' => 'Rejected',
            'no_review_needed' => 'No Review Needed',
            default => 'Unknown',
        };
    }

    // Check if item has been synced to stock adjustment
    public function isSynced()
    {
        return StockAdjustment::where('related_opname_item_id', $this->id)->exists();
    }

    // Get related stock adjustment
    public function stockAdjustment()
    {
        return StockAdjustment::where('related_opname_item_id', $this->id)->first();
    }

    // Sync to stock adjustment
    public function syncToStockAdjustment()
    {
        // Only sync approved items with discrepancy
        if ($this->review_status !== 'approved' || !$this->hasDiscrepancy()) {
            return null;
        }

        // Check if already synced
        if ($this->isSynced()) {
            return $this->stockAdjustment();
        }

        // Create adjustment from this opname item
        $adjustment = StockAdjustment::createFromOpnameItem($this);

        if ($adjustment) {
            // Apply adjustment to update actual stock
            $adjustment->applyAdjustment();
        }

        return $adjustment;
    }
}
