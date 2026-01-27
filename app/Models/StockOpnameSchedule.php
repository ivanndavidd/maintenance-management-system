<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockOpnameSchedule extends Model
{
    protected $fillable = [
        'schedule_code',
        'execution_date',
        'include_spareparts',
        'include_tools',
        'include_assets',
        'sparepart_locations',
        'asset_locations',
        'total_items',
        'completed_items',
        'cancelled_items',
        'created_by',
        'status',
        'notes',
        'execution_count',
        'missed_count',
        'ticket_status',
        'closed_at',
        'closed_by',
        'execution_type',
        'days_difference',
    ];

    protected $casts = [
        'execution_date' => 'date',
        'include_spareparts' => 'boolean',
        'include_tools' => 'boolean',
        'include_assets' => 'boolean',
        'sparepart_locations' => 'array',
        'asset_locations' => 'array',
        'closed_at' => 'datetime',
    ];

    // Relationships
    public function createdByUser()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function closedByUser()
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    public function executions()
    {
        return $this->hasMany(StockOpnameExecution::class, 'schedule_id');
    }

    // User assignments
    public function userAssignments()
    {
        return $this->hasMany(StockOpnameUserAssignment::class, 'schedule_id');
    }

    public function assignedUsers()
    {
        return $this->belongsToMany(User::class, 'stock_opname_user_assignments', 'schedule_id', 'user_id')
            ->withPivot('assignment_date', 'shift_type', 'is_active')
            ->withTimestamps();
    }

    // Many-to-many relationship with items through pivot table
    public function scheduleItems()
    {
        return $this->hasMany(StockOpnameScheduleItem::class, 'schedule_id');
    }

    // Get active items only
    public function activeItems()
    {
        return $this->scheduleItems()->where('is_active', true);
    }

    // Get spareparts in this schedule
    public function spareparts()
    {
        return $this->hasManyThrough(
            Sparepart::class,
            StockOpnameScheduleItem::class,
            'schedule_id',
            'id',
            'id',
            'item_id'
        )->where('stock_opname_schedule_items.item_type', 'sparepart')
         ->where('stock_opname_schedule_items.is_active', true);
    }

    // Get tools in this schedule
    public function tools()
    {
        return $this->hasManyThrough(
            Tool::class,
            StockOpnameScheduleItem::class,
            'schedule_id',
            'id',
            'id',
            'item_id'
        )->where('stock_opname_schedule_items.item_type', 'tool')
         ->where('stock_opname_schedule_items.is_active', true);
    }

    // Get assets in this schedule
    public function assets()
    {
        return $this->hasManyThrough(
            Asset::class,
            StockOpnameScheduleItem::class,
            'schedule_id',
            'id',
            'id',
            'item_id'
        )->where('stock_opname_schedule_items.item_type', 'asset')
         ->where('stock_opname_schedule_items.is_active', true);
    }

    // Get pending items
    public function pendingItems()
    {
        return $this->scheduleItems()->where('execution_status', 'pending');
    }

    // Get completed items
    public function completedItemsQuery()
    {
        return $this->scheduleItems()->where('execution_status', 'completed');
    }

    // Get cancelled items
    public function cancelledItemsQuery()
    {
        return $this->scheduleItems()->where('execution_status', 'cancelled');
    }

    // Get progress percentage
    public function getProgressPercentage()
    {
        if ($this->total_items == 0) {
            return 0;
        }

        return round(($this->completed_items / $this->total_items) * 100, 2);
    }

    // Check if schedule is within period
    public function isWithinPeriod()
    {
        $now = now()->toDateString();
        return $now == $this->execution_date->toDateString();
    }

    // Check if schedule is overdue
    public function isOverdue()
    {
        return now()->toDateString() > $this->execution_date->toDateString() && $this->status !== 'completed';
    }

    // Check if schedule is due soon (within 2 days)
    public function isDueSoon()
    {
        if ($this->status === 'completed' || $this->isOverdue()) {
            return false;
        }

        $daysRemaining = $this->getDaysRemaining();
        return $daysRemaining >= 0 && $daysRemaining <= 2;
    }

    // Generate schedule code with fixed logic
    public static function generateScheduleCode()
    {
        $date = now();
        $prefix = 'SOS';
        $dateFormat = $date->format('Ymd');

        // Get last schedule with schedule_code that matches today's date pattern
        $pattern = $prefix . '-' . $dateFormat . '-%';
        $lastSchedule = self::where('schedule_code', 'LIKE', $pattern)
            ->orderBy('schedule_code', 'desc')
            ->first();

        if ($lastSchedule && $lastSchedule->schedule_code) {
            $lastCode = $lastSchedule->schedule_code;
            $lastIncrement = (int) substr($lastCode, -3);
            $newIncrement = $lastIncrement + 1;
        } else {
            $newIncrement = 1;
        }

        return $prefix . '-' . $dateFormat . '-' . str_pad($newIncrement, 3, '0', STR_PAD_LEFT);
    }

    // Get days remaining until execution date
    public function getDaysRemaining()
    {
        $now = now()->startOfDay();
        $executionDate = $this->execution_date->startOfDay();

        // Calculate difference in days (as integer)
        $diff = $now->diffInDays($executionDate, false);

        // Return as integer (rounded)
        return (int) round($diff);
    }

    // Update completed items count
    public function updateCompletedCount()
    {
        $this->completed_items = $this->completedItemsQuery()->count();
        $this->save();
    }

    // Update cancelled items count
    public function updateCancelledCount()
    {
        $this->cancelled_items = $this->cancelledItemsQuery()->count();
        $this->save();
    }

    // Update progress (completed and cancelled counts)
    public function updateProgress()
    {
        $this->completed_items = $this->completedItemsQuery()->count();
        $this->cancelled_items = $this->cancelledItemsQuery()->count();
        $this->save();
    }

    // Check if all items completed
    public function isFullyCompleted()
    {
        return $this->total_items > 0 && $this->completed_items == $this->total_items;
    }

    // Alias for items relationship
    public function items()
    {
        return $this->scheduleItems();
    }

    // Analytics Methods

    // Get review statistics
    public function getReviewStats()
    {
        $completedItems = $this->items()->where('execution_status', 'completed')->get();

        return [
            'total_completed' => $completedItems->count(),
            'pending_review' => $completedItems->where('review_status', 'pending_review')->count(),
            'approved' => $completedItems->where('review_status', 'approved')->count(),
            'rejected' => $completedItems->where('review_status', 'rejected')->count(),
            'no_review_needed' => $completedItems->where('review_status', 'no_review_needed')->count(),
        ];
    }

    // Get discrepancy statistics
    public function getDiscrepancyStats()
    {
        $completedItems = $this->items()->where('execution_status', 'completed')->get();
        $itemsWithDiscrepancy = $completedItems->filter(fn($item) => $item->hasDiscrepancy());

        $positiveDiscrepancy = $itemsWithDiscrepancy->filter(fn($item) => $item->discrepancy_qty > 0);
        $negativeDiscrepancy = $itemsWithDiscrepancy->filter(fn($item) => $item->discrepancy_qty < 0);

        return [
            'total_items' => $completedItems->count(),
            'items_with_discrepancy' => $itemsWithDiscrepancy->count(),
            'items_without_discrepancy' => $completedItems->count() - $itemsWithDiscrepancy->count(),
            'positive_discrepancy_count' => $positiveDiscrepancy->count(),
            'negative_discrepancy_count' => $negativeDiscrepancy->count(),
            'total_positive_qty' => $positiveDiscrepancy->sum('discrepancy_qty'),
            'total_negative_qty' => abs($negativeDiscrepancy->sum('discrepancy_qty')),
            'total_positive_value' => $positiveDiscrepancy->sum('discrepancy_value'),
            'total_negative_value' => abs($negativeDiscrepancy->sum('discrepancy_value')),
            'accuracy_rate' => $completedItems->count() > 0
                ? round((($completedItems->count() - $itemsWithDiscrepancy->count()) / $completedItems->count()) * 100, 2)
                : 0,
        ];
    }

    // Get sync statistics
    public function getSyncStats()
    {
        $approvedItems = $this->items()
            ->where('review_status', 'approved')
            ->where('discrepancy_qty', '!=', 0)
            ->get();

        $syncedItems = $approvedItems->filter(fn($item) => $item->isSynced());

        return [
            'total_approved_with_discrepancy' => $approvedItems->count(),
            'synced' => $syncedItems->count(),
            'pending_sync' => $approvedItems->count() - $syncedItems->count(),
            'sync_rate' => $approvedItems->count() > 0
                ? round(($syncedItems->count() / $approvedItems->count()) * 100, 2)
                : 0,
        ];
    }

    // Get item type breakdown
    public function getItemTypeBreakdown()
    {
        return [
            'spareparts' => [
                'total' => $this->items()->where('item_type', 'sparepart')->count(),
                'completed' => $this->items()->where('item_type', 'sparepart')->where('execution_status', 'completed')->count(),
                'pending' => $this->items()->where('item_type', 'sparepart')->where('execution_status', 'pending')->count(),
            ],
            'tools' => [
                'total' => $this->items()->where('item_type', 'tool')->count(),
                'completed' => $this->items()->where('item_type', 'tool')->where('execution_status', 'completed')->count(),
                'pending' => $this->items()->where('item_type', 'tool')->where('execution_status', 'pending')->count(),
            ],
            'assets' => [
                'total' => $this->items()->where('item_type', 'asset')->count(),
                'completed' => $this->items()->where('item_type', 'asset')->where('execution_status', 'completed')->count(),
                'pending' => $this->items()->where('item_type', 'asset')->where('execution_status', 'pending')->count(),
            ],
        ];
    }

    // Get completion timeline data
    public function getCompletionTimeline()
    {
        return $this->items()
            ->where('execution_status', 'completed')
            ->selectRaw('DATE(executed_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get();
    }

    // Get user performance data
    public function getUserPerformance()
    {
        $completedItems = $this->items()->where('execution_status', 'completed')->get();

        $userStats = [];
        foreach ($completedItems->groupBy('executed_by') as $userId => $items) {
            $user = User::find($userId);
            if ($user) {
                $itemsWithDiscrepancy = $items->filter(fn($item) => $item->hasDiscrepancy());

                $userStats[] = [
                    'user_id' => $userId,
                    'user_name' => $user->name,
                    'total_items' => $items->count(),
                    'items_with_discrepancy' => $itemsWithDiscrepancy->count(),
                    'accuracy_rate' => round((($items->count() - $itemsWithDiscrepancy->count()) / $items->count()) * 100, 2),
                ];
            }
        }

        return collect($userStats)->sortByDesc('total_items')->values()->all();
    }

    // Get comprehensive analytics
    public function getAnalytics()
    {
        return [
            'overview' => [
                'total_items' => $this->total_items,
                'completed_items' => $this->completed_items,
                'pending_items' => $this->total_items - $this->completed_items - $this->cancelled_items,
                'cancelled_items' => $this->cancelled_items,
                'progress_percentage' => $this->getProgressPercentage(),
            ],
            'review' => $this->getReviewStats(),
            'discrepancy' => $this->getDiscrepancyStats(),
            'sync' => $this->getSyncStats(),
            'item_types' => $this->getItemTypeBreakdown(),
            'user_performance' => $this->getUserPerformance(),
        ];
    }

    /**
     * Check if schedule can be closed
     */
    public function canBeClosed()
    {
        // Can be closed if all items are completed
        return $this->ticket_status === 'open' &&
               $this->completed_items === $this->total_items;
    }

    /**
     * Close the ticket
     */
    public function closeTicket($userId)
    {
        if (!$this->canBeClosed()) {
            return false;
        }

        // Calculate execution type and days difference
        $executionDate = $this->execution_date;
        $closedDate = now();
        $daysDifference = $closedDate->diffInDays($executionDate, false);

        if ($daysDifference > 0) {
            // Closed before execution date
            $executionType = 'early';
            $daysValue = $daysDifference;
        } elseif ($daysDifference < 0) {
            // Closed after execution date
            $executionType = 'late';
            $daysValue = abs($daysDifference);
        } else {
            // Closed on time
            $executionType = 'ontime';
            $daysValue = 0;
        }

        $this->ticket_status = 'closed';
        $this->closed_at = $closedDate;
        $this->closed_by = $userId;
        $this->execution_type = $executionType;
        $this->days_difference = $daysValue;
        $this->status = 'completed';
        $this->save();

        return true;
    }

    /**
     * Get all item types in this schedule
     */
    public function getItemTypes()
    {
        $types = [];
        if ($this->include_spareparts) $types[] = 'Spareparts';
        if ($this->include_tools) $types[] = 'Tools';
        if ($this->include_assets) $types[] = 'Assets';
        return implode(', ', $types);
    }

    /**
     * Get all locations in this schedule
     */
    public function getAllLocations()
    {
        $locations = [];

        if ($this->sparepart_locations && is_array($this->sparepart_locations)) {
            $locations = array_merge($locations, $this->sparepart_locations);
        }

        if ($this->asset_locations && is_array($this->asset_locations)) {
            $locations = array_merge($locations, $this->asset_locations);
        }

        if ($this->include_tools && empty($locations)) {
            return '-';
        }

        return !empty($locations) ? implode(', ', array_unique($locations)) : '-';
    }

    /**
     * Get total discrepancy count
     */
    public function getTotalDiscrepancy()
    {
        return $this->scheduleItems()
            ->where('execution_status', 'completed')
            ->whereRaw('physical_quantity != system_quantity')
            ->count();
    }

    /**
     * Get execution date from schedule
     */
    public function getExecutionDate()
    {
        return $this->execution_date;
    }

    /**
     * Get assigned users names
     */
    public function getAssignedUsersNames()
    {
        return $this->assignedUsers()->pluck('name')->implode(', ');
    }
}
