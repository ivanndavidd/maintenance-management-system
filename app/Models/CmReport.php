<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CmReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'cm_request_id',
        'asset_id',
        'status',
        'problem_detail',
        'work_done',
        'notes',
        'submitted_by',
        'submitted_at',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
    ];

    public function cmRequest(): BelongsTo
    {
        return $this->belongsTo(CorrectiveMaintenanceRequest::class, 'cm_request_id');
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class, 'asset_id');
    }

    public function submitter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function getStatusBadgeClass(): string
    {
        return match ($this->status) {
            'done' => 'bg-success',
            'further_repair' => 'bg-warning text-dark',
            default => 'bg-secondary',
        };
    }

    public function getStatusLabel(): string
    {
        return match ($this->status) {
            'done' => 'Done',
            'further_repair' => 'Further Repair',
            default => ucfirst($this->status),
        };
    }
}
