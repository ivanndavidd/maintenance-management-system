<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class PmTaskReport extends TenantModels
{
    use HasFactory;

    protected $fillable = [
        'pm_task_id',
        'description',
        'photos',
        'status',
        'admin_comments',
        'submitted_by',
        'submitted_at',
        'reviewed_by',
        'reviewed_at',
    ];

    protected $casts = [
        'photos' => 'array',
        'submitted_at' => 'datetime',
        'reviewed_at' => 'datetime',
    ];

    public function task(): BelongsTo
    {
        return $this->belongsTo(PmTask::class, 'pm_task_id');
    }

    public function submitter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function furtherRepairAssets(): BelongsToMany
    {
        return $this->belongsToMany(Asset::class, 'pm_task_report_assets')
            ->withPivot('notes')
            ->withTimestamps();
    }

    public function getStatusBadgeClass(): string
    {
        return match ($this->status) {
            'submitted' => 'bg-info',
            'approved' => 'bg-success',
            'revision_needed' => 'bg-warning text-dark',
            default => 'bg-secondary',
        };
    }

    public function getStatusLabel(): string
    {
        return match ($this->status) {
            'submitted' => 'Submitted',
            'approved' => 'Approved',
            'revision_needed' => 'Revision Needed',
            default => ucfirst($this->status),
        };
    }
}
