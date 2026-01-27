<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShiftSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'start_date',
        'end_date',
        'status',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    /**
     * Get the user who created this schedule
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get all shift assignments for this schedule
     */
    public function assignments()
    {
        return $this->hasMany(ShiftAssignment::class);
    }

    /**
     * Get assignments grouped by day and shift
     */
    public function getAssignmentsByDayAndShift()
    {
        $grouped = [];
        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
        $shifts = ['shift_1', 'shift_2', 'shift_3'];

        foreach ($days as $day) {
            $grouped[$day] = [];
            foreach ($shifts as $shift) {
                $grouped[$day][$shift] = $this->assignments()
                    ->where('day_of_week', $day)
                    ->where('shift_type', $shift)
                    ->with('user')
                    ->get();
            }
        }

        return $grouped;
    }

    /**
     * Get total working hours for a user in this schedule
     */
    public function getUserTotalHours($userId)
    {
        return $this->assignments()
            ->where('user_id', $userId)
            ->sum('working_hours');
    }

    /**
     * Get users assigned in a specific shift
     */
    public function getUsersInShift($dayOfWeek, $shiftType)
    {
        return $this->assignments()
            ->where('day_of_week', $dayOfWeek)
            ->where('shift_type', $shiftType)
            ->with('user')
            ->get()
            ->pluck('user');
    }

    /**
     * Check if schedule is active
     */
    public function isActive()
    {
        return $this->status === 'active';
    }

    /**
     * Activate this schedule
     */
    public function activate()
    {
        // Deactivate other schedules that overlap
        self::where('status', 'active')
            ->where(function ($query) {
                $query->whereBetween('start_date', [$this->start_date, $this->end_date])
                    ->orWhereBetween('end_date', [$this->start_date, $this->end_date])
                    ->orWhere(function ($q) {
                        $q->where('start_date', '<=', $this->start_date)
                          ->where('end_date', '>=', $this->end_date);
                    });
            })
            ->update(['status' => 'completed']);

        $this->update(['status' => 'active']);
    }

    /**
     * Get shift type display name
     */
    public static function getShiftTypeName($shiftType)
    {
        $names = [
            'shift_1' => 'Shift 1 (22:00-05:00)',
            'shift_2' => 'Shift 2 (06:00-13:00)',
            'shift_3' => 'Shift 3 (14:00-21:00)',
        ];

        return $names[$shiftType] ?? $shiftType;
    }

    /**
     * Get shift type times
     */
    public static function getShiftTimes($shiftType)
    {
        $times = [
            'shift_1' => ['start' => '22:00:00', 'end' => '05:00:00', 'hours' => 7],
            'shift_2' => ['start' => '06:00:00', 'end' => '13:00:00', 'hours' => 7],
            'shift_3' => ['start' => '14:00:00', 'end' => '21:00:00', 'hours' => 7],
        ];

        return $times[$shiftType] ?? null;
    }
}
