<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\CorrectiveMaintenanceRequest;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'employee_id',
        'phone',
        'department_id',
        'is_active',
        'last_login_at',
        'email_verified_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */

    protected $connection = 'site';
    protected $hidden = ['password', 'remember_token'];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'last_login_at' => 'datetime',
        ];
    }

    /**
     * RELATIONSHIPS
     */

    /**
     * User belongs to a department
     */
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * CMR tickets assigned to this user
     */
    public function assignedCmr()
    {
        return $this->hasMany(CorrectiveMaintenanceRequest::class, 'assigned_to');
    }

    /**
     * CMR tickets where this user is a technician
     */
    public function cmrAsTechnician()
    {
        return $this->belongsToMany(
            CorrectiveMaintenanceRequest::class,
            'corrective_maintenance_technicians',
            'user_id',
            'cm_request_id',
        )->withTimestamps();
    }

    /**
     * Jobs created by this user (as admin/creator)
     */
    public function createdJobs()
    {
        return $this->hasMany(MaintenanceJob::class, 'created_by');
    }

    /**
     * Job completion logs for KPI tracking
     */
    public function jobCompletionLogs()
    {
        return $this->hasMany(JobCompletionLog::class, 'user_id');
    }

    /**
     * ACCESSORS & MUTATORS
     */

    /**
     * Get user's full name with employee ID
     */
    public function getFullNameAttribute()
    {
        return "{$this->name} ({$this->employee_id})";
    }

    /**
     * Check if user is active
     */
    public function isActive()
    {
        return $this->is_active == true;
    }

    /**
     * Get user's avatar initials
     */
    public function getInitialsAttribute()
    {
        $words = explode(' ', $this->name);
        if (count($words) >= 2) {
            return strtoupper(substr($words[0], 0, 1) . substr($words[1], 0, 1));
        }
        return strtoupper(substr($this->name, 0, 2));
    }

    /**
     * SCOPES
     */

    /**
     * Scope to get only active users
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get users by department
     */
    public function scopeByDepartment($query, $departmentId)
    {
        return $query->where('department_id', $departmentId);
    }

    /**
     * Scope to get users by role
     */
    public function scopeByRole($query, $roleName)
    {
        return $query->role($roleName);
    }

    /**
     * Scope to get operators only (staff maintenance)
     */
    public function scopeOperators($query)
    {
        return $query->role('staff_maintenance');
    }

    /**
     * Scope to get admins only
     */
    public function scopeAdmins($query)
    {
        return $query->role('admin');
    }

    public function siteAccessRequests()
    {
        return $this->hasMany(SiteAccessRequest::class, 'target_user_id');
    }

    public function isSuper(): bool
    {
        // Check from central DB so super status is consistent across all sites
        static $cache = [];

        if (isset($cache[$this->email])) {
            return $cache[$this->email];
        }

        try {
            $centralSuper = DB::connection('central')->table('users')
                ->where('email', $this->email)
                ->value('super');

            $cache[$this->email] = (bool) $centralSuper;
        } catch (\Exception $e) {
            $cache[$this->email] = false;
        }

        return $cache[$this->email];
    }
}
