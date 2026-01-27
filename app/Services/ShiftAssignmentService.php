<?php

namespace App\Services;

use App\Models\ShiftSchedule;
use App\Models\ShiftAssignment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class ShiftAssignmentService
{
    /**
     * Get users currently on duty based on date and time
     *
     * @param Carbon|null $dateTime The datetime to check (defaults to now)
     * @return Collection Collection of User models on duty
     */
    public function getUsersOnDuty(?Carbon $dateTime = null): Collection
    {
        $dateTime = $dateTime ?? Carbon::now();

        $date = $dateTime->copy()->startOfDay();
        $hour = (int) $dateTime->format('H');
        $dayOfWeek = strtolower($dateTime->format('l')); // 'monday', 'tuesday', etc.

        // Get the shift ID for this hour
        $shiftId = ShiftAssignment::getShiftIdForHour($hour);

        if (!$shiftId) {
            return collect();
        }

        // Find active schedule that covers this date
        $schedule = ShiftSchedule::where('status', 'active')
            ->where('start_date', '<=', $date)
            ->where('end_date', '>=', $date)
            ->first();

        if (!$schedule) {
            return collect();
        }

        // For overnight shift (shift_id = 1), we need special handling
        // Hours 22-23 belong to TODAY's shift but stored as TOMORROW's duty day
        // Hours 0-5 belong to TODAY's shift and stored as TODAY's duty day
        $dutyDay = $dayOfWeek;
        $targetSchedule = $schedule;

        if ($shiftId === 1) {
            if ($hour >= 22 && $hour <= 23) {
                // Hours 22-23 today belong to tomorrow's Shift 1
                // So we look for assignments where day_of_week = tomorrow
                $tomorrow = $dateTime->copy()->addDay();
                $dutyDay = strtolower($tomorrow->format('l'));

                // Check if tomorrow falls into next week's schedule
                if ($tomorrow->gt($schedule->end_date)) {
                    $targetSchedule = ShiftSchedule::where('status', 'active')
                        ->where('start_date', '<=', $tomorrow)
                        ->where('end_date', '>=', $tomorrow)
                        ->first();
                }
            }
            // Hours 0-5 belong to today's Shift 1, duty day is today (no change needed)
        }

        if (!$targetSchedule) {
            return collect();
        }

        // Get assignments for this shift
        $assignments = ShiftAssignment::where('shift_schedule_id', $targetSchedule->id)
            ->where('day_of_week', $dutyDay)
            ->where('shift_id', $shiftId)
            ->where(function ($query) use ($hour) {
                // Check if the hour is in selected_hours
                $query->whereJsonContains('selected_hours', $hour)
                    ->orWhereNull('selected_hours');
            })
            ->with('user')
            ->get();

        // Handle replacement/cancellation
        $users = collect();

        foreach ($assignments as $assignment) {
            // Check if assignment has a change action
            if ($assignment->change_action === 'cancelled') {
                continue; // Skip cancelled assignments
            }

            if ($assignment->change_action === 'replaced' && $assignment->new_user_id) {
                // Use replacement user
                $newUser = User::find($assignment->new_user_id);
                if ($newUser) {
                    $users->push($newUser);
                }
            } else {
                // Use original assigned user
                if ($assignment->user) {
                    $users->push($assignment->user);
                }
            }
        }

        return $users->unique('id');
    }

    /**
     * Get shift info for a given datetime
     *
     * @param Carbon|null $dateTime
     * @return array|null
     */
    public function getShiftInfo(?Carbon $dateTime = null): ?array
    {
        $dateTime = $dateTime ?? Carbon::now();
        $hour = (int) $dateTime->format('H');
        $shiftId = ShiftAssignment::getShiftIdForHour($hour);

        if (!$shiftId) {
            return null;
        }

        $shiftNames = [
            1 => 'Shift 1 (22:00-05:00)',
            2 => 'Shift 2 (06:00-13:00)',
            3 => 'Shift 3 (14:00-21:00)',
        ];

        $shiftRanges = [
            1 => ['start' => '22:00', 'end' => '05:00'],
            2 => ['start' => '06:00', 'end' => '13:00'],
            3 => ['start' => '14:00', 'end' => '21:00'],
        ];

        return [
            'shift_id' => $shiftId,
            'name' => $shiftNames[$shiftId],
            'range' => $shiftRanges[$shiftId],
            'date' => $dateTime->format('Y-m-d'),
            'time' => $dateTime->format('H:i'),
            'day_of_week' => strtolower($dateTime->format('l')),
        ];
    }

    /**
     * Get active schedule for a date
     *
     * @param Carbon|null $date
     * @return ShiftSchedule|null
     */
    public function getActiveSchedule(?Carbon $date = null): ?ShiftSchedule
    {
        $date = $date ?? Carbon::now();

        return ShiftSchedule::where('status', 'active')
            ->where('start_date', '<=', $date)
            ->where('end_date', '>=', $date)
            ->first();
    }

    /**
     * Check if there are technicians on duty at a given time
     *
     * @param Carbon|null $dateTime
     * @return bool
     */
    public function hasTechniciansOnDuty(?Carbon $dateTime = null): bool
    {
        return $this->getUsersOnDuty($dateTime)->isNotEmpty();
    }
}
