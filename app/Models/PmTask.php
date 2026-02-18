<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PmTask extends TenantModels
{
    use HasFactory;

    protected $fillable = [
        'pm_spr_group_id',
        'pm_schedule_date_id',
        'task_name',
        'task_description',
        'frequency',
        'equipment_type',
        'assigned_shift_id',
        'assigned_user_id',
        'status',
        'completed_at',
        'completed_by',
        'completion_notes',
        'sort_order',
        // Calendar fields
        'task_date',
        // Recurring fields
        'recurrence_pattern',
        'recurrence_interval',
        'recurrence_days',
        'recurrence_day_of_month',
        'recurrence_start_date',
        'recurrence_end_date',
        'is_recurring',
        'parent_task_id',
        'reminder_sent_at',
    ];

    protected $casts = [
        'completed_at' => 'datetime',
        'task_date' => 'date',
        'recurrence_start_date' => 'date',
        'recurrence_end_date' => 'date',
        'is_recurring' => 'boolean',
        'reminder_sent_at' => 'datetime',
    ];

    const STATUS_PENDING = 'pending';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_COMPLETED = 'completed';
    const STATUS_SKIPPED = 'skipped';

    // Frequency options
    public static function getFrequencies(): array
    {
        return [
            '1_week' => '1 Minggu',
            '2_weeks' => '2 Minggu',
            '3_weeks' => '3 Minggu',
            '1_month' => '1 Bulan',
            '2_months' => '2 Bulan',
            '3_months' => '3 Bulan',
            '6_months' => '6 Bulan',
            '1_year' => '1 Tahun',
        ];
    }

    public function getFrequencyLabelAttribute(): string
    {
        return self::getFrequencies()[$this->frequency] ?? $this->frequency;
    }

    public static function getStatuses(): array
    {
        return [
            self::STATUS_PENDING => 'Pending',
            self::STATUS_IN_PROGRESS => 'In Progress',
            self::STATUS_COMPLETED => 'Completed',
            self::STATUS_SKIPPED => 'Skipped',
        ];
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'secondary',
            self::STATUS_IN_PROGRESS => 'warning',
            self::STATUS_COMPLETED => 'success',
            self::STATUS_SKIPPED => 'dark',
            default => 'secondary',
        };
    }

    // Relationships
    public function sprGroup(): BelongsTo
    {
        return $this->belongsTo(PmSprGroup::class, 'pm_spr_group_id');
    }

    public function scheduleDate(): BelongsTo
    {
        return $this->belongsTo(PmScheduleDate::class, 'pm_schedule_date_id');
    }

    public function isStandalone(): bool
    {
        return $this->pm_schedule_date_id !== null && $this->pm_spr_group_id === null;
    }

    public function getAssignedShiftNameAttribute(): ?string
    {
        $names = [
            1 => 'Shift 1 (22:00 - 05:00)',
            2 => 'Shift 2 (06:00 - 13:00)',
            3 => 'Shift 3 (14:00 - 21:00)',
        ];
        return $names[$this->assigned_shift_id] ?? null;
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    public function completedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(PmTaskLog::class)->orderByDesc('created_at');
    }

    public function parentTask(): BelongsTo
    {
        return $this->belongsTo(PmTask::class, 'parent_task_id');
    }

    public function childTasks(): HasMany
    {
        return $this->hasMany(PmTask::class, 'parent_task_id');
    }

    public function reports(): HasMany
    {
        return $this->hasMany(PmTaskReport::class);
    }

    public function latestReport(): HasOne
    {
        return $this->hasOne(PmTaskReport::class)->latestOfMany();
    }

    // Get schedule through relationships
    public function getScheduleAttribute()
    {
        return $this->sprGroup?->cleaningGroup?->schedule;
    }

    // Get cleaning group
    public function getCleaningGroupAttribute()
    {
        return $this->sprGroup?->cleaningGroup;
    }

    // Mark as completed
    public function markCompleted(?int $userId = null, ?string $notes = null): bool
    {
        $this->update([
            'status' => self::STATUS_COMPLETED,
            'completed_at' => now(),
            'completed_by' => $userId ?? auth()->id(),
            'completion_notes' => $notes,
        ]);

        $this->logs()->create([
            'user_id' => $userId ?? auth()->id(),
            'action' => 'completed',
            'notes' => $notes,
        ]);

        return true;
    }

    // Mark as in progress
    public function markInProgress(?int $userId = null): bool
    {
        $this->update([
            'status' => self::STATUS_IN_PROGRESS,
        ]);

        $this->logs()->create([
            'user_id' => $userId ?? auth()->id(),
            'action' => 'started',
        ]);

        return true;
    }
}
