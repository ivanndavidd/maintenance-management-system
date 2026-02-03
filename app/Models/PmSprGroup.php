<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PmSprGroup extends Model
{
    use HasFactory;

    protected $fillable = [
        'pm_cleaning_group_id',
        'name',
        'sort_order',
    ];

    // Relationships
    public function cleaningGroup(): BelongsTo
    {
        return $this->belongsTo(PmCleaningGroup::class, 'pm_cleaning_group_id');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(PmTask::class)->orderBy('sort_order');
    }

    // Get schedule through cleaning group
    public function getScheduleAttribute()
    {
        return $this->cleaningGroup?->schedule;
    }

    // Get tasks count
    public function getTasksCountAttribute(): int
    {
        return $this->tasks()->count();
    }

    // Get completed tasks count
    public function getCompletedTasksCountAttribute(): int
    {
        return $this->tasks()->where('status', 'completed')->count();
    }
}
