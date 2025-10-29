<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'report_code',
        'job_id',
        'user_id',
        'validated_by',
        'work_start',
        'work_end',
        'downtime_minutes',
        'work_performed',
        'issues_found',
        'recommendations',
        'machine_condition',
        'status',
        'admin_comments',
        'validated_at',
        'attachments',
    ];

    protected $casts = [
        'work_start' => 'datetime',
        'work_end' => 'datetime',
        'validated_at' => 'datetime',
        'downtime_minutes' => 'integer',
        'attachments' => 'array',
    ];

    /**
     * RELATIONSHIPS
     */

    /**
     * Report belongs to a maintenance job
     */
    public function job()
    {
        return $this->belongsTo(MaintenanceJob::class, 'job_id');
    }

    /**
     * Report submitted by a user (technician)
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Report validated by admin
     */
    public function validator()
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    /**
     * ACCESSORS
     */

    /**
     * Get status badge color
     */
    public function getStatusBadgeAttribute()
    {
        return match ($this->status) {
            'pending' => 'warning',
            'approved' => 'success',
            'rejected' => 'danger',
            'draft' => 'secondary',
            default => 'secondary',
        };
    }

    /**
     * Get machine condition badge color
     */
    public function getConditionBadgeAttribute()
    {
        return match ($this->machine_condition) {
            'excellent' => 'success',
            'good' => 'info',
            'fair' => 'warning',
            'poor' => 'danger',
            'critical' => 'danger',
            default => 'secondary',
        };
    }

    /**
     * Calculate work duration in hours
     */
    public function getWorkDurationAttribute()
    {
        if (!$this->work_start || !$this->work_end) {
            return 0;
        }

        return $this->work_start->diffInHours($this->work_end);
    }

    /**
     * Get work duration formatted
     */
    public function getWorkDurationFormattedAttribute()
    {
        if (!$this->work_start || !$this->work_end) {
            return 'N/A';
        }

        $hours = $this->work_start->diffInHours($this->work_end);
        $minutes = $this->work_start->diffInMinutes($this->work_end) % 60;

        return sprintf('%dh %dm', $hours, $minutes);
    }

    /**
     * SCOPES
     */

    /**
     * Scope to get pending reports
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope to get approved reports
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope to get rejected reports
     */
    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    /**
     * Scope to get reports by user
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope to get reports by job
     */
    public function scopeByJob($query, $jobId)
    {
        return $query->where('job_id', $jobId);
    }
}
