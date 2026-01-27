<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockOpnameUserAssignment extends Model
{
    protected $fillable = [
        'schedule_id',
        'user_id',
        'assignment_date',
        'shift_type',
        'is_active',
    ];

    protected $casts = [
        'assignment_date' => 'date',
        'is_active' => 'boolean',
    ];

    // Relationships
    public function schedule()
    {
        return $this->belongsTo(StockOpnameSchedule::class, 'schedule_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Get user's completion statistics for this schedule
    public function getUserStats()
    {
        $schedule = $this->schedule;

        $totalItems = $schedule->scheduleItems()->count();
        $completedByUser = $schedule->scheduleItems()
            ->where('executed_by', $this->user_id)
            ->where('execution_status', 'completed')
            ->count();

        $pendingItems = $schedule->pendingItems()->count();

        return [
            'total_items' => $totalItems,
            'completed_by_user' => $completedByUser,
            'pending_items' => $pendingItems,
            'completion_percentage' => $totalItems > 0 ? round(($completedByUser / $totalItems) * 100, 2) : 0,
        ];
    }
}
