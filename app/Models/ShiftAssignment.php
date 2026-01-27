<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShiftAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'shift_schedule_id',
        'user_id',
        'day_of_week',
        'original_calendar_day',
        'shift_id',
        'selected_hours',
        'column_index',
        'color',
        'notes',
        'change_action',
        'new_user_id',
        'change_effective_date',
        'change_reason',
        'changed_by',
    ];

    protected $casts = [
        'selected_hours' => 'array',
    ];

    /**
     * Get the shift schedule
     */
    public function shiftSchedule()
    {
        return $this->belongsTo(ShiftSchedule::class);
    }

    /**
     * Get the assigned user
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get shift change logs
     */
    public function changeLogs()
    {
        return $this->hasMany(ShiftChangeLog::class);
    }

    /**
     * Get the new user (for replacement)
     */
    public function newUser()
    {
        return $this->belongsTo(User::class, 'new_user_id');
    }

    /**
     * Get the user who made the change
     */
    public function changedByUser()
    {
        return $this->belongsTo(User::class, 'changed_by');
    }

    /**
     * Get day name in Indonesian
     */
    public function getDayNameAttribute()
    {
        $days = [
            'monday' => 'Senin',
            'tuesday' => 'Selasa',
            'wednesday' => 'Rabu',
            'thursday' => 'Kamis',
            'friday' => 'Jumat',
            'saturday' => 'Sabtu',
            'sunday' => 'Minggu',
        ];

        return $days[$this->day_of_week] ?? $this->day_of_week;
    }

    /**
     * Get shift name and time range
     */
    public function getShiftNameAttribute()
    {
        $shifts = [
            1 => 'Shift 1 (22:00-05:00)',
            2 => 'Shift 2 (06:00-13:00)',
            3 => 'Shift 3 (14:00-21:00)',
        ];

        return $shifts[$this->shift_id] ?? 'Unknown Shift';
    }

    /**
     * Get shift hours based on shift_id
     */
    public static function getShiftHours($shiftId)
    {
        $shifts = [
            1 => array_merge(range(22, 23), range(0, 5)), // 22, 23, 0, 1, 2, 3, 4, 5
            2 => range(6, 13),   // 6-13
            3 => range(14, 21),  // 14-21
        ];

        return $shifts[$shiftId] ?? [];
    }

    /**
     * Determine which shift an hour belongs to
     */
    public static function getShiftIdForHour($hour)
    {
        if ($hour >= 22 || $hour <= 5) {
            return 1; // Shift 1: 22:00-05:00
        } elseif ($hour >= 6 && $hour <= 13) {
            return 2; // Shift 2: 06:00-13:00
        } elseif ($hour >= 14 && $hour <= 21) {
            return 3; // Shift 3: 14:00-21:00
        }

        return null;
    }

    /**
     * Determine the actual duty day for a given calendar day and hour
     *
     * For Shift 1 (overnight shift):
     * - Hours 22-23 on Day X belong to Shift 1 of Day X+1
     * - Hours 0-5 on Day X belong to Shift 1 of Day X
     *
     * Example:
     * - Monday 22:00 -> Tuesday Shift 1
     * - Tuesday 00:00 -> Tuesday Shift 1
     *
     * @param string $calendarDay The day on the calendar (when hour occurs)
     * @param int $hour The hour (0-23)
     * @return string The actual duty day
     */
    public static function getActualDutyDay($calendarDay, $hour)
    {
        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];

        // Hours 22-23 belong to NEXT day's Shift 1
        if ($hour >= 22 && $hour <= 23) {
            $currentIndex = array_search($calendarDay, $days);
            if ($currentIndex === false) {
                return $calendarDay; // Invalid day, return as-is
            }

            // Next day (wrap around to monday after sunday)
            $nextIndex = ($currentIndex + 1) % 7;
            return $days[$nextIndex];
        }

        // All other hours belong to the same day
        return $calendarDay;
    }

    /**
     * Get working hours for a shift
     */
    public static function getShiftWorkingHours($shiftId)
    {
        $hours = [
            1 => 8, // 22:00-05:00 = 8 hours
            2 => 8, // 06:00-13:00 = 8 hours
            3 => 8, // 14:00-21:00 = 8 hours
        ];

        return $hours[$shiftId] ?? 0;
    }

    /**
     * Generate color for user if not set
     */
    public static function generateColorForUser($userId)
    {
        $colors = [
            '#FF6B6B', '#4ECDC4', '#45B7D1', '#FFA07A', '#98D8C8',
            '#F7DC6F', '#BB8FCE', '#85C1E2', '#F8B739', '#52B788',
            '#FF8B94', '#A8E6CF', '#FFD3B6', '#FFAAA5', '#FF8C94',
        ];

        return $colors[$userId % count($colors)];
    }

    /**
     * Calculate total working hours for a user in a schedule
     */
    public static function calculateUserHours($scheduleId, $userId)
    {
        $assignments = self::where('shift_schedule_id', $scheduleId)
            ->where('user_id', $userId)
            ->get();

        $totalHours = 0;
        foreach ($assignments as $assignment) {
            // Use selected_hours count if available, otherwise use full shift hours
            if ($assignment->selected_hours && is_array($assignment->selected_hours)) {
                $totalHours += count($assignment->selected_hours);
            } else {
                $totalHours += self::getShiftWorkingHours($assignment->shift_id);
            }
        }

        return $totalHours;
    }

    /**
     * Get all assignments grouped by day, hour, and column
     * Converts shift_id back to hourly view for display
     */
    public static function getScheduleGrid($scheduleId)
    {
        $assignments = self::where('shift_schedule_id', $scheduleId)
            ->with(['user', 'newUser'])
            ->get();

        $grid = [];
        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];

        // Initialize empty grid (24 hours x 7 days x 4 columns)
        foreach ($days as $day) {
            $grid[$day] = [];
            for ($hour = 0; $hour < 24; $hour++) {
                $grid[$day][$hour] = [
                    0 => null,
                    1 => null,
                    2 => null,
                    3 => null,
                ];
            }
        }

        // Fill grid by expanding shift assignments to all hours in that shift
        foreach ($assignments as $assignment) {
            // Use selected_hours if available, otherwise use all shift hours
            $hours = $assignment->selected_hours ?? self::getShiftHours($assignment->shift_id);

            foreach ($hours as $hour) {
                // NEW LOGIC: Always display hours on their DUTY DAY (day_of_week)
                // Hours 22-23 for Monday Shift 1 will show on MONDAY column (not Sunday)
                // This allows drag selection from 22 to 05 in same column
                $displayDay = $assignment->day_of_week;

                $grid[$displayDay][$hour][$assignment->column_index] = $assignment;
            }
        }

        return $grid;
    }

    /**
     * Get the calendar day where an hour should be displayed
     *
     * This is the reverse of getActualDutyDay()
     * - If duty_day=Tuesday, hour=22 -> Display on Monday
     * - If duty_day=Tuesday, hour=0 -> Display on Tuesday
     *
     * @param string $dutyDay The stored duty day
     * @param int $hour The hour (0-23)
     * @return string The calendar day for display
     */
    public static function getCalendarDayForDisplay($dutyDay, $hour)
    {
        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];

        // Hours 22-23 are stored under next day, so display on previous day
        if ($hour >= 22 && $hour <= 23) {
            $currentIndex = array_search($dutyDay, $days);
            if ($currentIndex === false) {
                return $dutyDay; // Invalid day, return as-is
            }

            // Previous day (wrap around to sunday before monday)
            $prevIndex = ($currentIndex - 1 + 7) % 7;
            return $days[$prevIndex];
        }

        // All other hours display on the same day they're stored
        return $dutyDay;
    }
}
