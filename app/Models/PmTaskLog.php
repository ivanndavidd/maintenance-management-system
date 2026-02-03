<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PmTaskLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'pm_task_id',
        'user_id',
        'action',
        'notes',
    ];

    // Relationships
    public function task(): BelongsTo
    {
        return $this->belongsTo(PmTask::class, 'pm_task_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getActionLabelAttribute(): string
    {
        return match ($this->action) {
            'created' => 'Created',
            'started' => 'Started',
            'completed' => 'Completed',
            'skipped' => 'Skipped',
            'reassigned' => 'Reassigned',
            default => ucfirst($this->action),
        };
    }

    public function getActionBadgeAttribute(): string
    {
        return match ($this->action) {
            'created' => 'info',
            'started' => 'warning',
            'completed' => 'success',
            'skipped' => 'dark',
            'reassigned' => 'primary',
            default => 'secondary',
        };
    }
}
