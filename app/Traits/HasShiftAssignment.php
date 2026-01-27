<?php

namespace App\Traits;

use App\Models\ShiftSchedule;
use App\Models\User;
use App\Notifications\TaskAssignedToShiftNotification;

trait HasShiftAssignment
{
    /**
     * Get the shift schedule relationship
     */
    public function shiftSchedule()
    {
        return $this->belongsTo(ShiftSchedule::class);
    }

    /**
     * Get users assigned in this shift
     */
    public function getShiftUsers()
    {
        if (!$this->shift_schedule_id || !$this->shift_date || !$this->shift_type) {
            return collect([]);
        }

        $dayOfWeek = strtolower(\Carbon\Carbon::parse($this->shift_date)->format('l'));

        return $this->shiftSchedule
            ->getUsersInShift($dayOfWeek, $this->shift_type);
    }

    /**
     * Get shift display name
     */
    public function getShiftDisplayName()
    {
        if (!$this->shift_type) {
            return 'No Shift Assigned';
        }

        return ShiftSchedule::getShiftTypeName($this->shift_type);
    }

    /**
     * Check if task has shift assignment
     */
    public function hasShiftAssignment()
    {
        return !is_null($this->shift_schedule_id) && !is_null($this->shift_type);
    }

    /**
     * Notify shift users about task assignment
     */
    public function notifyShiftUsers($taskType)
    {
        if (!$this->hasShiftAssignment()) {
            return;
        }

        $users = $this->getShiftUsers();

        if ($users->isEmpty()) {
            return;
        }

        $shiftTimes = ShiftSchedule::getShiftTimes($this->shift_type);
        $shiftInfo = [
            'shift_name' => $this->getShiftDisplayName(),
            'shift_date' => \Carbon\Carbon::parse($this->shift_date)->format('d M Y'),
            'shift_time' => $shiftTimes['start'] . ' - ' . $shiftTimes['end'],
        ];

        foreach ($users as $user) {
            $user->notify(new TaskAssignedToShiftNotification($this, $taskType, $shiftInfo));
        }
    }

    /**
     * Scope to filter by shift
     */
    public function scopeWithShift($query, $shiftType = null, $shiftDate = null)
    {
        if ($shiftType) {
            $query->where('shift_type', $shiftType);
        }

        if ($shiftDate) {
            $query->where('shift_date', $shiftDate);
        }

        return $query;
    }

    /**
     * Scope to get tasks with active shifts
     */
    public function scopeHasActiveShift($query)
    {
        return $query->whereHas('shiftSchedule', function ($q) {
            $q->where('status', 'active');
        });
    }
}
