<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaintenanceJob extends Model
{
    use HasFactory;

    protected $fillable = [
        'job_code',
        'title',
        'description',
        'machine_id',
        'assigned_to',
        'created_by',
        'type',
        'priority',
        'status',
        'scheduled_date',
        'started_at',
        'completed_at',
        'estimated_duration',
        'actual_duration',
        'notes',
        'is_recurring',
        'recurrence_type',
        'recurrence_interval',
        'recurrence_end_date',
        'parent_job_id',
    ];

    protected $casts = [
        'scheduled_date' => 'date',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'estimated_duration' => 'integer',
        'actual_duration' => 'integer',
        'is_recurring' => 'boolean',
        'recurrence_interval' => 'integer',
        'recurrence_end_date' => 'date',
    ];

    /**
     * RELATIONSHIPS
     */

    public function machine()
    {
        return $this->belongsTo(Machine::class);
    }

    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Alias for creator() - for backward compatibility
     * ✅ ADDED THIS ALIAS
     */
    public function assignedBy()
    {
        return $this->creator();
    }

    public function workReports()
    {
        return $this->hasMany(WorkReport::class, 'job_id');
    }

    public function parentJob()
    {
        return $this->belongsTo(MaintenanceJob::class, 'parent_job_id');
    }

    public function childJobs()
    {
        return $this->hasMany(MaintenanceJob::class, 'parent_job_id');
    }

    /**
     * ACCESSORS
     */

    public function getStatusBadgeAttribute()
    {
        return match ($this->status) {
            'pending' => 'warning',
            'in_progress' => 'primary',
            'completed' => 'success',
            'cancelled' => 'secondary',
            default => 'secondary',
        };
    }

    public function getPriorityBadgeAttribute()
    {
        return match ($this->priority) {
            'low' => 'secondary',
            'medium' => 'info',
            'high' => 'warning',
            'critical' => 'danger',
            default => 'secondary',
        };
    }

    public function getTypeBadgeAttribute()
    {
        return match ($this->type) {
            'preventive' => 'success',
            'breakdown' => 'danger',
            'corrective' => 'warning',
            'inspection' => 'info',
            default => 'secondary',
        };
    }

    public function isOverdue()
    {
        if (!$this->scheduled_date || $this->status === 'completed') {
            return false;
        }

        return now()->gt($this->scheduled_date);
    }

    /**
     * SCOPES
     */

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeOverdue($query)
    {
        return $query->where('status', '!=', 'completed')->where('scheduled_date', '<', now());
    }

    public function scopeByPriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }
}
