<?php

namespace App\Services;

use App\Models\StockOpnameSchedule;
use App\Models\StockOpnameScheduleItem;
use App\Models\StockOpnameUserAssignment;
use App\Models\ShiftAssignment;
use App\Models\Sparepart;
use App\Models\Tool;
use App\Models\Asset;
use Illuminate\Support\Facades\DB;

class StockOpnameService
{
    /**
     * Auto-assign all users who duty during the schedule period
     *
     * @param StockOpnameSchedule $schedule
     * @return int Number of users assigned
     */
    /**
     * Manually assign users to stock opname schedule (NEW SYSTEM)
     *
     * @param StockOpnameSchedule $schedule
     * @param array $userIds Array of user IDs to assign
     * @param string $executionDate Date when stock opname will be executed
     * @return int Number of users assigned
     */
    public function assignUsersManually(StockOpnameSchedule $schedule, array $userIds, string $executionDate)
    {
        $assignmentCount = 0;

        foreach ($userIds as $userId) {
            // Check if user is already assigned to this schedule
            $existingAssignment = StockOpnameUserAssignment::where('schedule_id', $schedule->id)
                ->where('user_id', $userId)
                ->first();

            if (!$existingAssignment) {
                StockOpnameUserAssignment::create([
                    'schedule_id' => $schedule->id,
                    'user_id' => $userId,
                    'assignment_date' => $executionDate,
                    'shift_type' => null, // No shift tracking in manual assignment
                    'is_active' => true,
                ]);
                $assignmentCount++;
            }
        }

        return $assignmentCount;
    }

    /**
     * Auto-assign users based on shift schedule (OLD SYSTEM - DEPRECATED)
     * Keeping for backwards compatibility
     */
    public function autoAssignUsers(StockOpnameSchedule $schedule)
    {
        // Find active shift schedules that overlap with stock opname period
        // ShiftSchedule has start_date and end_date (actual dates)
        $overlappingShiftSchedules = \App\Models\ShiftSchedule::where('status', 'active')
            ->where(function ($query) use ($schedule) {
                // Check if shift schedule overlaps with stock opname period
                $query->whereBetween('start_date', [$schedule->start_date, $schedule->end_date])
                    ->orWhereBetween('end_date', [$schedule->start_date, $schedule->end_date])
                    ->orWhere(function ($q) use ($schedule) {
                        // Shift schedule completely covers stock opname period
                        $q->where('start_date', '<=', $schedule->start_date)
                          ->where('end_date', '>=', $schedule->end_date);
                    });
            })
            ->pluck('id');

        if ($overlappingShiftSchedules->isEmpty()) {
            // No active shift schedules found for this period
            return 0;
        }

        // Get all unique users from shift assignments in those shift schedules
        $assignedUsers = ShiftAssignment::whereIn('shift_schedule_id', $overlappingShiftSchedules)
            ->select('user_id', 'shift_id')
            ->get()
            ->unique('user_id'); // Get unique users only

        $assignmentCount = 0;

        foreach ($assignedUsers as $assignment) {
            // Check if user is already assigned to this schedule
            $existingAssignment = StockOpnameUserAssignment::where('schedule_id', $schedule->id)
                ->where('user_id', $assignment->user_id)
                ->first();

            if (!$existingAssignment) {
                // Convert shift_id (1,2,3) to shift_type name (shift_1, shift_2, shift_3)
                $shiftTypeName = 'shift_' . $assignment->shift_id;

                StockOpnameUserAssignment::create([
                    'schedule_id' => $schedule->id,
                    'user_id' => $assignment->user_id,
                    'assignment_date' => now(),
                    'shift_type' => $shiftTypeName,
                    'is_active' => true,
                ]);
                $assignmentCount++;
            }
        }

        return $assignmentCount;
    }

    /**
     * Create schedule items based on selected locations and item types
     *
     * @param StockOpnameSchedule $schedule
     * @param array $data
     * @return int Total items created
     */
    public function createScheduleItems(StockOpnameSchedule $schedule, array $data)
    {
        $totalItems = 0;

        // Add spareparts if included
        if ($schedule->include_spareparts && !empty($data['sparepart_locations'])) {
            $spareparts = Sparepart::whereIn('location', $data['sparepart_locations'])
                ->get();

            foreach ($spareparts as $sparepart) {
                StockOpnameScheduleItem::create([
                    'schedule_id' => $schedule->id,
                    'item_type' => 'sparepart',
                    'item_id' => $sparepart->id,
                    'execution_status' => 'pending',
                    'is_active' => true,
                ]);
                $totalItems++;
            }
        }

        // Add tools if included (all tools, no location filter)
        if ($schedule->include_tools) {
            $tools = Tool::all();

            foreach ($tools as $tool) {
                StockOpnameScheduleItem::create([
                    'schedule_id' => $schedule->id,
                    'item_type' => 'tool',
                    'item_id' => $tool->id,
                    'execution_status' => 'pending',
                    'is_active' => true,
                ]);
                $totalItems++;
            }
        }

        // Add assets if included
        if ($schedule->include_assets && !empty($data['asset_locations'])) {
            $assets = Asset::whereIn('location', $data['asset_locations'])
                ->where('status', 'active')
                ->get();

            foreach ($assets as $asset) {
                StockOpnameScheduleItem::create([
                    'schedule_id' => $schedule->id,
                    'item_type' => 'asset',
                    'item_id' => $asset->id,
                    'execution_status' => 'pending',
                    'is_active' => true,
                ]);
                $totalItems++;
            }
        }

        // Update total_items on the schedule
        $schedule->total_items = $totalItems;
        $schedule->save();

        return $totalItems;
    }

    /**
     * Get available locations for spareparts
     *
     * @return array
     */
    public function getSparepartLocations()
    {
        return Sparepart::whereNotNull('location')
            ->where('location', '!=', '')
            ->distinct()
            ->pluck('location')
            ->sort()
            ->values()
            ->toArray();
    }

    /**
     * Get available locations for assets
     *
     * @return array
     */
    public function getAssetLocations()
    {
        return Asset::where('status', 'active')
            ->whereNotNull('location')
            ->where('location', '!=', '')
            ->distinct()
            ->pluck('location')
            ->sort()
            ->values()
            ->toArray();
    }

    /**
     * Get item count preview based on selections
     *
     * @param array $data
     * @return array
     */
    public function getItemCountPreview(array $data)
    {
        $counts = [
            'spareparts' => 0,
            'tools' => 0,
            'assets' => 0,
            'total' => 0,
        ];

        if (!empty($data['include_spareparts']) && !empty($data['sparepart_locations'])) {
            $counts['spareparts'] = Sparepart::whereIn('location', $data['sparepart_locations'])
                ->count();
        }

        if (!empty($data['include_tools'])) {
            $counts['tools'] = Tool::count();
        }

        if (!empty($data['include_assets']) && !empty($data['asset_locations'])) {
            $counts['assets'] = Asset::whereIn('location', $data['asset_locations'])
                ->where('status', 'active')
                ->count();
        }

        $counts['total'] = $counts['spareparts'] + $counts['tools'] + $counts['assets'];

        return $counts;
    }

    /**
     * Get user's assigned schedules
     *
     * @param int $userId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getUserAssignedSchedules($userId)
    {
        return StockOpnameSchedule::whereHas('userAssignments', function ($query) use ($userId) {
                $query->where('user_id', $userId)
                    ->where('is_active', true);
            })
            ->whereIn('status', ['active', 'in_progress'])
            ->with(['scheduleItems' => function ($query) {
                $query->where('is_active', true);
            }])
            ->orderBy('start_date', 'desc')
            ->get();
    }

    /**
     * Get pending items for a user in a specific schedule
     *
     * @param int $scheduleId
     * @param int $userId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getUserPendingItems($scheduleId, $userId)
    {
        return StockOpnameScheduleItem::where('schedule_id', $scheduleId)
            ->where('execution_status', 'pending')
            ->where('is_active', true)
            ->orderBy('item_type')
            ->get();
    }

    /**
     * Get user statistics for a schedule
     *
     * @param int $scheduleId
     * @param int $userId
     * @return array
     */
    public function getUserScheduleStats($scheduleId, $userId)
    {
        $schedule = StockOpnameSchedule::findOrFail($scheduleId);

        $totalItems = $schedule->scheduleItems()->where('is_active', true)->count();
        $completedByUser = $schedule->scheduleItems()
            ->where('executed_by', $userId)
            ->where('execution_status', 'completed')
            ->count();
        $pendingItems = $schedule->pendingItems()->count();
        $completedItems = $schedule->completedItemsQuery()->count();
        $cancelledItems = $schedule->cancelledItemsQuery()->count();

        return [
            'total_items' => $totalItems,
            'completed_by_user' => $completedByUser,
            'pending_items' => $pendingItems,
            'completed_items' => $completedItems,
            'cancelled_items' => $cancelledItems,
            'user_completion_percentage' => $totalItems > 0 ? round(($completedByUser / $totalItems) * 100, 2) : 0,
            'overall_completion_percentage' => $schedule->getProgressPercentage(),
        ];
    }

    /**
     * Execute opname for an item
     *
     * @param int $itemId
     * @param int $userId
     * @param int $physicalQuantity
     * @param string|null $notes
     * @return StockOpnameScheduleItem
     */
    public function executeOpname($itemId, $userId, $physicalQuantity, $notes = null)
    {
        $item = StockOpnameScheduleItem::findOrFail($itemId);

        // Check if item is not already completed
        if ($item->execution_status !== 'pending') {
            throw new \Exception('Item is already ' . $item->execution_status);
        }

        // Mark as completed
        $item->markCompleted($userId, $physicalQuantity, $notes);

        return $item;
    }

    /**
     * Check if schedule should be auto-completed
     *
     * @param StockOpnameSchedule $schedule
     * @return bool
     */
    public function checkAndUpdateScheduleStatus(StockOpnameSchedule $schedule)
    {
        // If all items are completed, mark schedule as completed
        if ($schedule->isFullyCompleted()) {
            $schedule->status = 'completed';
            $schedule->save();
            return true;
        }

        // If schedule has started (has some completed items), mark as in_progress
        if ($schedule->completed_items > 0 && $schedule->status === 'active') {
            $schedule->status = 'in_progress';
            $schedule->save();
        }

        return false;
    }
}
