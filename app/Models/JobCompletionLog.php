<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobCompletionLog extends TenantModels
{
    use HasFactory;

    protected $fillable = [
        'job_id',
        'user_id',
        'scheduled_date',
        'completed_at',
        'days_late',
        'completion_status',
        'job_code',
        'job_title',
        'job_type',
        'priority',
    ];

    protected $casts = [
        'scheduled_date' => 'date',
        'completed_at' => 'datetime',
        'days_late' => 'integer',
    ];

    /**
     * RELATIONSHIPS
     */

    public function job()
    {
        return $this->belongsTo(MaintenanceJob::class, 'job_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * ACCESSORS
     */

    public function getStatusBadgeAttribute()
    {
        return match ($this->completion_status) {
            'on_time' => 'success',
            'late' => 'danger',
            'early' => 'info',
            default => 'secondary',
        };
    }
}
