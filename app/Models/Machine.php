<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Machine extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'category_id',
        'model',
        'brand',
        'serial_number',
        'purchase_date',
        'purchase_cost',
        'warranty_expiry',
        'department_id',
        'location',
        'status',
        'specifications',
        'notes',
        'last_maintenance_date',
        'next_maintenance_date',
        'maintenance_interval_days',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'warranty_expiry' => 'date',
        'last_maintenance_date' => 'date',
        'next_maintenance_date' => 'date',
        'purchase_cost' => 'decimal:2',
        'maintenance_interval_days' => 'integer',
    ];

    /**
     * RELATIONSHIPS
     */

    /**
     * Machine belongs to a category
     */
    public function category()
    {
        return $this->belongsTo(MachineCategory::class, 'category_id');
    }

    /**
     * Machine belongs to a department
     */
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Machine has many maintenance jobs
     */
    public function maintenanceJobs()
    {
        return $this->hasMany(MaintenanceJob::class, 'machine_id');
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
            'operational' => 'success',
            'maintenance' => 'warning',
            'breakdown' => 'danger',
            'retired' => 'secondary',
            default => 'secondary',
        };
    }

    /**
     * Get type from category (for backward compatibility)
     */
    public function getTypeAttribute()
    {
        return $this->category ? $this->category->name : 'N/A';
    }

    /**
     * Check if machine is operational
     */
    public function isOperational()
    {
        return $this->status === 'operational';
    }

    /**
     * Check if maintenance is due
     */
    public function isMaintenanceDue()
    {
        if (!$this->next_maintenance_date) {
            return false;
        }

        return now()->gte($this->next_maintenance_date);
    }

    /**
     * SCOPES
     */

    /**
     * Scope to get operational machines
     */
    public function scopeOperational($query)
    {
        return $query->where('status', 'operational');
    }

    /**
     * Scope to get machines by department
     */
    public function scopeByDepartment($query, $departmentId)
    {
        return $query->where('department_id', $departmentId);
    }

    /**
     * Scope to get machines by category
     */
    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    /**
     * Scope to get machines needing maintenance
     */
    public function scopeMaintenanceDue($query)
    {
        return $query
            ->where('next_maintenance_date', '<=', now())
            ->orWhere('status', 'maintenance');
    }
}
