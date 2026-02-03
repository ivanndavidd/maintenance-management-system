<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PmScheduleDate extends Model
{
    use HasFactory;

    protected $fillable = [
        'pm_schedule_id',
        'schedule_date',
        'sort_order',
    ];

    protected $casts = [
        'schedule_date' => 'date',
    ];

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(PmSchedule::class, 'pm_schedule_id');
    }

    public function cleaningGroups(): HasMany
    {
        return $this->hasMany(PmCleaningGroup::class, 'pm_schedule_date_id')->orderBy('sort_order');
    }

    public function standaloneTasks(): HasMany
    {
        return $this->hasMany(PmTask::class, 'pm_schedule_date_id')->whereNull('pm_spr_group_id')->orderBy('sort_order');
    }

    public function getTasksCountAttribute(): int
    {
        return PmTask::whereHas('sprGroup.cleaningGroup', function ($query) {
            $query->where('pm_schedule_date_id', $this->id);
        })->count();
    }
}
