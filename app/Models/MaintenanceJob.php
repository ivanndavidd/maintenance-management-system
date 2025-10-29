<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaintenanceJob extends Model
{
    use HasFactory;

    protected $fillable = [
        'job_code', 'title', 'description', 'machine_id', 'assigned_to',
        'created_by', 'type', 'priority', 'status', 'scheduled_date',
        'started_at', 'completed_at', 'estimated_duration', 'actual_duration', 'notes'
    ];

    protected $casts = [
        'scheduled_date' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($job) {
            // Auto generate job code
            $count = static::whereYear('created_at', date('Y'))->count() + 1;
            $job->job_code = 'JOB' . date('Y') . str_pad($count, 5, '0', STR_PAD_LEFT);
        });
    }

    // Relationships
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

    public function workReport()
    {
        return $this->hasOne(WorkReport::class, 'job_id');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeOverdue($query)
    {
        return $query->where('status', '!=', 'completed')
                     ->where('scheduled_date', '<', now());
    }

    public function scopeMyTasks($query, $userId)
    {
        return $query->where('assigned_to', $userId);
    }
}