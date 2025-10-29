<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'report_code', 'job_id', 'user_id', 'validated_by', 'work_start',
        'work_end', 'downtime_minutes', 'work_performed', 'issues_found',
        'recommendations', 'machine_condition', 'status', 'admin_comments',
        'validated_at', 'attachments'
    ];

    protected $casts = [
        'work_start' => 'datetime',
        'work_end' => 'datetime',
        'validated_at' => 'datetime',
        'attachments' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($report) {
            // Auto generate report code
            $count = static::whereYear('created_at', date('Y'))->count() + 1;
            $report->report_code = 'RPT' . date('Y') . str_pad($count, 5, '0', STR_PAD_LEFT);
        });
    }

    // Relationships
    public function job()
    {
        return $this->belongsTo(MaintenanceJob::class, 'job_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function validator()
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    public function parts()
    {
        return $this->belongsToMany(Part::class, 'report_parts')
                    ->withPivot('quantity', 'cost')
                    ->withTimestamps();
    }

    // Calculated attributes
    public function getTotalPartsCostAttribute()
    {
        return $this->parts->sum(function ($part) {
            return $part->pivot->quantity * $part->pivot->cost;
        });
    }

    public function getWorkDurationAttribute()
    {
        if ($this->work_start && $this->work_end) {
            return $this->work_start->diffInMinutes($this->work_end);
        }
        return 0;
    }
}