<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PmSchedule extends TenantModels
{
    use HasFactory;

    protected $fillable = ['scheduled_month', 'title', 'description', 'status', 'created_by'];

    protected $casts = [
        'scheduled_month' => 'date',
    ];

    const STATUS_DRAFT = 'draft';
    const STATUS_ACTIVE = 'active';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';

    public static function getStatuses(): array
    {
        return [
            self::STATUS_DRAFT => 'Draft',
            self::STATUS_ACTIVE => 'Active',
            self::STATUS_COMPLETED => 'Completed',
            self::STATUS_CANCELLED => 'Cancelled',
        ];
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_DRAFT => 'secondary',
            self::STATUS_ACTIVE => 'primary',
            self::STATUS_COMPLETED => 'success',
            self::STATUS_CANCELLED => 'danger',
            default => 'secondary',
        };
    }

    // Relationships
    public function scheduleDates(): HasMany
    {
        return $this->hasMany(PmScheduleDate::class)->orderByDesc('schedule_date');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Get all tasks through eager-loaded relationships (no extra queries)
    public function getAllTasksAttribute()
    {
        return $this->scheduleDates->flatMap(function ($date) {
            return $date->cleaningGroups->flatMap(function ($group) {
                return $group->sprGroups->flatMap(function ($spr) {
                    return $spr->tasks;
                });
            });
        });
    }

    // Get task statistics
    public function getTaskStatsAttribute(): array
    {
        $tasks = $this->all_tasks;
        $total = $tasks->count();
        $completed = $tasks->where('status', 'completed')->count();
        $pending = $tasks->where('status', 'pending')->count();
        $inProgress = $tasks->where('status', 'in_progress')->count();

        return [
            'total' => $total,
            'completed' => $completed,
            'pending' => $pending,
            'in_progress' => $inProgress,
            'progress' => $total > 0 ? round(($completed / $total) * 100) : 0,
        ];
    }
}
