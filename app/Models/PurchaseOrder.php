<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseOrder extends Model
{
    protected $fillable = [
        'po_number',
        'total_items',
        'total_quantity',
        'has_unlisted_items',
        'total_price',
        'supplier',
        'order_date',
        'expected_delivery_date',
        'actual_delivery_date',
        'status',
        'ordered_by',
        'received_by',
        'notes',
        'approval_status',
        'approver_id',
        'approved_by',
        'approved_at',
        'rejection_reason',
        'is_reorder',
        'original_po_id',
        'return_reason',
        'returned_at',
        'returned_by',
        'cancellation_type',
        'cancellation_reason',
        'cancelled_at',
        'cancelled_by',
    ];

    protected $casts = [
        'order_date' => 'date',
        'expected_delivery_date' => 'date',
        'actual_delivery_date' => 'date',
        'approved_at' => 'datetime',
        'returned_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'total_price' => 'decimal:2',
        'is_reorder' => 'boolean',
        'has_unlisted_items' => 'boolean',
        'total_items' => 'integer',
        'total_quantity' => 'integer',
    ];

    // Purchase Order Items relationship (multi-item cart)
    public function items()
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    // Get listed items (from master data)
    public function listedItems()
    {
        return $this->items()->where('is_unlisted', false);
    }

    // Get unlisted items (not in master data yet)
    public function unlistedItems()
    {
        return $this->items()->where('is_unlisted', true);
    }

    public function orderedByUser()
    {
        return $this->belongsTo(User::class, 'ordered_by');
    }

    public function receivedByUser()
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    // Approver relationship (user who is tagged to approve)
    public function approver()
    {
        return $this->belongsTo(User::class, 'approver_id');
    }

    // Approved by relationship (user who actually approved/rejected)
    public function approvedByUser()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // Returned by relationship
    public function returnedByUser()
    {
        return $this->belongsTo(User::class, 'returned_by');
    }

    // Cancelled by relationship
    public function cancelledByUser()
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    // Original PO relationship (for reorders)
    public function originalPO()
    {
        return $this->belongsTo(PurchaseOrder::class, 'original_po_id');
    }

    // Reordered POs relationship
    public function reorders()
    {
        return $this->hasMany(PurchaseOrder::class, 'original_po_id');
    }

    // Generate PO number with fixed logic (like Tool ID fix)
    public static function generatePONumber()
    {
        $date = now();
        $prefix = 'PO';
        $dateFormat = $date->format('Ymd');

        // Get last PO with po_number that matches today's date pattern
        $pattern = $prefix . $dateFormat . '%';
        $lastPO = self::where('po_number', 'LIKE', $pattern)
            ->orderBy('po_number', 'desc')
            ->first();

        if ($lastPO && $lastPO->po_number) {
            $lastCode = $lastPO->po_number;
            $lastIncrement = (int) substr($lastCode, -3);
            $newIncrement = $lastIncrement + 1;
        } else {
            $newIncrement = 1;
        }

        return $prefix . $dateFormat . str_pad($newIncrement, 3, '0', STR_PAD_LEFT);
    }

    // Check if PO is fully received (all items)
    public function isFullyReceived()
    {
        $totalItems = $this->items()->count();
        if ($totalItems === 0) {
            return false;
        }

        $receivedItems = $this->items()->where('status', 'received')->count();
        return $receivedItems === $totalItems;
    }

    // Check if PO is partially received
    public function isPartiallyReceived()
    {
        $hasReceived = $this->items()->where('quantity_received', '>', 0)->exists();
        return $hasReceived && !$this->isFullyReceived();
    }

    // Calculate total items ordered
    public function getTotalItemsOrdered()
    {
        return $this->items()->count();
    }

    // Calculate total quantity ordered
    public function getTotalQuantityOrdered()
    {
        return $this->items()->sum('quantity_ordered');
    }

    // Calculate total quantity received
    public function getTotalQuantityReceived()
    {
        return $this->items()->sum('quantity_received');
    }

    // Calculate grand total (sum of all item subtotals)
    public function calculateGrandTotal()
    {
        return $this->items()->sum('subtotal');
    }

    // Update PO summary fields
    public function updateSummary()
    {
        $this->total_items = $this->getTotalItemsOrdered();
        $this->total_quantity = $this->getTotalQuantityOrdered();
        $this->total_price = $this->calculateGrandTotal();
        $this->has_unlisted_items = $this->unlistedItems()->exists();

        // Update PO status based on items
        if ($this->isFullyReceived()) {
            $this->status = 'received';
        } elseif ($this->isPartiallyReceived()) {
            $this->status = 'partial_received';
        }

        $this->save();
    }

    // Check if all items are compliant
    public function allItemsCompliant()
    {
        $totalItems = $this->items()->count();
        if ($totalItems === 0) {
            return false;
        }

        $compliantItems = $this->items()->where('compliance_status', 'compliant')->count();
        return $compliantItems === $totalItems;
    }

    // Check if any item is non-compliant
    public function hasNonCompliantItems()
    {
        return $this->items()->where('compliance_status', 'non_compliant')->exists();
    }

    // Check if all compliant items added to stock
    public function allCompliantItemsAddedToStock()
    {
        $compliantItems = $this->items()->where('compliance_status', 'compliant')->count();
        if ($compliantItems === 0) {
            return false;
        }

        $addedItems = $this->items()
            ->where('compliance_status', 'compliant')
            ->where('added_to_stock', true)
            ->count();

        return $addedItems === $compliantItems;
    }

    // Approval Methods
    public function isPending()
    {
        return $this->approval_status === 'pending';
    }

    public function isApproved()
    {
        return $this->approval_status === 'approved';
    }

    public function isRejected()
    {
        return $this->approval_status === 'rejected';
    }

    public function approve($userId)
    {
        $this->approval_status = 'approved';
        $this->approved_by = $userId;
        $this->approved_at = now();
        $this->save();
    }

    public function reject($userId, $reason = null)
    {
        $this->approval_status = 'rejected';
        $this->approved_by = $userId;
        $this->approved_at = now();
        $this->rejection_reason = $reason;
        $this->save();
    }

    public function canBeApprovedBy($userId)
    {
        // Check if user is the tagged approver or is an admin
        $user = User::find($userId);
        return $this->approver_id == $userId || ($user && $user->hasRole('admin'));
    }

    public function getApprovalStatusBadge()
    {
        return match($this->approval_status) {
            'pending' => '<span class="badge bg-warning">Pending Approval</span>',
            'approved' => '<span class="badge bg-success">Approved</span>',
            'rejected' => '<span class="badge bg-danger">Rejected</span>',
            default => '<span class="badge bg-secondary">Unknown</span>',
        };
    }

    // Get status flow data for visualization (multi-item version)
    public function getStatusFlowData()
    {
        $steps = [
            [
                'id' => 1,
                'name' => 'Created',
                'icon' => 'fa-file-alt',
                'status' => 'completed',
                'date' => $this->created_at,
                'user' => $this->orderedByUser,
                'description' => 'Purchase order created with ' . $this->total_items . ' item(s)'
            ],
            [
                'id' => 2,
                'name' => 'Approval',
                'icon' => 'fa-user-check',
                'status' => $this->approval_status === 'pending' ? 'active' :
                           ($this->approval_status === 'approved' ? 'completed' : 'rejected'),
                'date' => $this->approved_at,
                'user' => $this->approvedByUser,
                'description' => $this->approval_status === 'pending' ? 'Waiting for approval' :
                                ($this->approval_status === 'approved' ? 'Order approved' : 'Order rejected'),
                'rejection_reason' => $this->rejection_reason
            ],
            [
                'id' => 3,
                'name' => 'Ordered',
                'icon' => 'fa-shopping-cart',
                'status' => $this->approval_status === 'approved' ? 'completed' :
                           ($this->approval_status === 'pending' ? 'pending' : 'cancelled'),
                'date' => $this->approval_status === 'approved' ? $this->approved_at : null,
                'user' => null,
                'description' => 'Order sent to supplier'
            ],
            [
                'id' => 4,
                'name' => 'Goods Received',
                'icon' => 'fa-truck',
                'status' => $this->status === 'received' ? 'completed' :
                           ($this->status === 'partial_received' ? 'active' :
                           ($this->approval_status === 'approved' && $this->status === 'pending' ? 'active' : 'pending')),
                'date' => $this->actual_delivery_date,
                'user' => $this->receivedByUser,
                'description' => $this->status === 'received' ? 'All items received' :
                                ($this->status === 'partial_received' ? 'Partially received' : 'Awaiting delivery')
            ],
            [
                'id' => 5,
                'name' => 'Quality Check',
                'icon' => 'fa-clipboard-check',
                'status' => $this->allItemsCompliant() ? 'completed' :
                           ($this->hasNonCompliantItems() ? 'rejected' :
                           (($this->status === 'received' || $this->status === 'partial_received') ? 'active' : 'pending')),
                'date' => null, // Item-level timestamps now
                'user' => null,
                'description' => $this->allItemsCompliant() ? 'All items verified - compliant' :
                                ($this->hasNonCompliantItems() ? 'Some items non-compliant' : 'Awaiting inspection')
            ],
            [
                'id' => 6,
                'name' => 'Stock Added',
                'icon' => 'fa-warehouse',
                'status' => $this->allCompliantItemsAddedToStock() ? 'completed' :
                           ($this->allItemsCompliant() ? 'active' : 'pending'),
                'date' => null, // Item-level timestamps now
                'user' => null,
                'description' => $this->allCompliantItemsAddedToStock() ? 'All compliant items added to stock' :
                                ($this->allItemsCompliant() ? 'Ready to add to stock' : 'Waiting for compliance')
            ]
        ];

        // Add return/reorder step if any item is non-compliant
        if ($this->hasNonCompliantItems()) {
            $steps[] = [
                'id' => 7,
                'name' => 'Return & Reorder',
                'icon' => 'fa-undo',
                'status' => $this->returned_at ? 'completed' : 'active',
                'date' => $this->returned_at,
                'user' => $this->returnedByUser,
                'description' => $this->returned_at ? 'Items returned, new PO created' : 'Processing return',
                'return_reason' => $this->return_reason,
                'is_warning' => true
            ];
        }

        return $steps;
    }

    // Get current step in the flow (multi-item version)
    public function getCurrentStep()
    {
        if ($this->allCompliantItemsAddedToStock()) {
            return 6;
        } elseif ($this->hasNonCompliantItems()) {
            return $this->returned_at ? 7 : 5;
        } elseif ($this->allItemsCompliant()) {
            return 5;
        } elseif ($this->status === 'received' || $this->status === 'partial_received') {
            return 4;
        } elseif ($this->approval_status === 'approved') {
            return 3;
        } elseif ($this->approval_status === 'pending') {
            return 2;
        } else {
            return 1;
        }
    }

    // Return goods and create reorder
    public function returnAndReorder($userId, $returnReason)
    {
        // Mark as returned
        $this->returned_at = now();
        $this->returned_by = $userId;
        $this->return_reason = $returnReason;
        $this->save();

        // Create new PO with same details but marked as reorder
        $newPO = $this->replicate(['po_number', 'approved_at', 'approved_by', 'received_by',
                                   'actual_delivery_date', 'returned_at', 'returned_by',
                                   'total_items', 'total_quantity', 'has_unlisted_items', 'total_price']);

        $newPO->po_number = self::generatePONumber();
        $newPO->is_reorder = true;
        $newPO->original_po_id = $this->id;
        $newPO->approval_status = 'pending'; // Need re-approval
        $newPO->status = 'pending';
        $newPO->notes = "REORDER - Original PO: {$this->po_number}. Reason: {$returnReason}";
        $newPO->save();

        // Copy all items from original PO (reset their status)
        foreach ($this->items as $originalItem) {
            $newItem = $originalItem->replicate([
                'purchase_order_id',
                'quantity_received',
                'status',
                'compliance_status',
                'compliance_notes',
                'added_to_stock',
                'stock_added_at'
            ]);

            $newItem->purchase_order_id = $newPO->id;
            $newItem->quantity_received = 0;
            $newItem->status = 'pending';
            $newItem->compliance_status = null;
            $newItem->compliance_notes = null;
            $newItem->added_to_stock = false;
            $newItem->stock_added_at = null;
            $newItem->save();
        }

        // Update new PO summary
        $newPO->updateSummary();

        return $newPO;
    }

    /**
     * Cancel purchase order with tracking
     *
     * @param int $userId User who is cancelling the PO
     * @param string $cancellationType Type of cancellation (cancelled_by_vendor, cancelled_by_user, internal_issue, budget_constraint, duplicate_order, item_unavailable, other)
     * @param string $cancellationReason Detailed reason for cancellation
     * @return bool
     */
    public function cancel($userId, $cancellationType, $cancellationReason)
    {
        // Check if PO can be cancelled
        if ($this->status === 'cancelled') {
            return false; // Already cancelled
        }

        // Prevent cancellation if items already added to stock
        $hasStockItems = $this->items()->where('added_to_stock', true)->exists();
        if ($hasStockItems) {
            throw new \Exception('Cannot cancel PO: Some items have already been added to stock. Please reverse the stock first.');
        }

        // Update cancellation fields
        $this->status = 'cancelled';
        $this->cancellation_type = $cancellationType;
        $this->cancellation_reason = $cancellationReason;
        $this->cancelled_at = now();
        $this->cancelled_by = $userId;
        $this->save();

        // Cancel all pending items
        foreach ($this->items as $item) {
            if ($item->status !== 'received' && !$item->added_to_stock) {
                $item->update(['status' => 'cancelled']);
            }
        }

        return true;
    }

    /**
     * Check if PO can be cancelled
     *
     * @return bool
     */
    public function canBeCancelled()
    {
        // Cannot cancel if already cancelled
        if ($this->status === 'cancelled') {
            return false;
        }

        // Cannot cancel if any item has been added to stock
        if ($this->items()->where('added_to_stock', true)->exists()) {
            return false;
        }

        // Can cancel at any other stage (pending approval, approved/ordered, receiving, quality check)
        return true;
    }

    /**
     * Check if PO is cancelled
     *
     * @return bool
     */
    public function isCancelled()
    {
        return $this->status === 'cancelled';
    }

    /**
     * Get cancellation type badge
     *
     * @return string
     */
    public function getCancellationTypeBadge()
    {
        if (!$this->cancellation_type) {
            return '';
        }

        return match($this->cancellation_type) {
            'cancelled_by_vendor' => '<span class="badge bg-warning text-dark">Cancelled by Vendor</span>',
            'cancelled_by_user' => '<span class="badge bg-info">Cancelled by User</span>',
            'internal_issue' => '<span class="badge bg-danger">Internal Issue</span>',
            'budget_constraint' => '<span class="badge bg-warning">Budget Constraint</span>',
            'duplicate_order' => '<span class="badge bg-secondary">Duplicate Order</span>',
            'item_unavailable' => '<span class="badge bg-danger">Item Unavailable</span>',
            'other' => '<span class="badge bg-secondary">Other</span>',
            default => '<span class="badge bg-secondary">Unknown</span>',
        };
    }

    /**
     * Get cancellation type label
     *
     * @return string
     */
    public function getCancellationTypeLabel()
    {
        if (!$this->cancellation_type) {
            return '-';
        }

        return match($this->cancellation_type) {
            'cancelled_by_vendor' => 'Cancelled by Vendor',
            'cancelled_by_user' => 'Cancelled by User',
            'internal_issue' => 'Internal Issue',
            'budget_constraint' => 'Budget Constraint',
            'duplicate_order' => 'Duplicate Order',
            'item_unavailable' => 'Item Unavailable',
            'other' => 'Other',
            default => 'Unknown',
        };
    }
}
