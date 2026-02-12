<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PmCleaningGroup extends TenantModels
{
    use HasFactory;

    protected $fillable = ['pm_schedule_date_id', 'name', 'sort_order'];

    // Relationships
    public function scheduleDate(): BelongsTo
    {
        return $this->belongsTo(PmScheduleDate::class, 'pm_schedule_date_id');
    }

    public function sprGroups(): HasMany
    {
        return $this->hasMany(PmSprGroup::class)->orderBy('sort_order');
    }

    // Get total tasks count
    public function getTasksCountAttribute(): int
    {
        return PmTask::whereHas('sprGroup', function ($query) {
            $query->where('pm_cleaning_group_id', $this->id);
        })->count();
    }

    // Get SPR count
    public function getSprCountAttribute(): int
    {
        return $this->sprGroups()->count();
    }
}
