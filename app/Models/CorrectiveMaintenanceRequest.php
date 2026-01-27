<?php

namespace App\Models;

use App\Services\ShiftAssignmentService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class CorrectiveMaintenanceRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_number',
        'requestor_name',
        'requestor_email',
        'requestor_phone',
        'requestor_department',
        'location',
        'equipment_name',
        'equipment_id',
        'priority',
        'problem_category',
        'problem_description',
        'additional_notes',
        'attachment_path',
        'status',
        'assigned_to',
        'assigned_at',
        'work_notes',
        'resolution',
        'started_at',
        'completed_at',
        'received_at',
        'in_progress_at',
        'received_email_sent_at',
        'progress_email_sent_at',
        'completed_email_sent_at',
        'handled_by',
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'received_at' => 'datetime',
        'in_progress_at' => 'datetime',
        'received_email_sent_at' => 'datetime',
        'progress_email_sent_at' => 'datetime',
        'completed_email_sent_at' => 'datetime',
    ];

    /**
     * Generate unique ticket number
     */
    public static function generateTicketNumber(): string
    {
        $prefix = 'CMR'; // Corrective Maintenance Request
        $date = now()->format('Ymd');

        // Get the last ticket number for today
        $lastTicket = self::where('ticket_number', 'like', "{$prefix}-{$date}-%")
            ->orderBy('ticket_number', 'desc')
            ->first();

        if ($lastTicket) {
            $lastNumber = (int) substr($lastTicket->ticket_number, -4);
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }

        return "{$prefix}-{$date}-{$newNumber}";
    }

    /**
     * Get priority badge class
     */
    public function getPriorityBadgeClass(): string
    {
        return match($this->priority) {
            'low' => 'bg-secondary',
            'medium' => 'bg-info',
            'high' => 'bg-warning',
            'critical' => 'bg-danger',
            default => 'bg-secondary',
        };
    }

    /**
     * Get problem category label
     */
    public function getProblemCategoryLabel(): string
    {
        return match($this->problem_category) {
            'conveyor_totebox' => 'Conveyor Totebox',
            'conveyor_paket' => 'Conveyor Paket',
            'lift_merah' => 'Lift Merah',
            'lift_kuning' => 'Lift Kuning',
            'chute' => 'Chute',
            'others' => 'Others',
            default => $this->problem_category ?? '-',
        };
    }

    /**
     * Get problem category icon
     */
    public function getProblemCategoryIcon(): string
    {
        return match($this->problem_category) {
            'conveyor_totebox' => 'fa-box',
            'conveyor_paket' => 'fa-boxes-stacked',
            'lift_merah' => 'fa-elevator',
            'lift_kuning' => 'fa-elevator',
            'chute' => 'fa-arrow-down-wide-short',
            'others' => 'fa-ellipsis-h',
            default => 'fa-question',
        };
    }

    /**
     * Get problem category badge color
     */
    public function getProblemCategoryBadgeClass(): string
    {
        return match($this->problem_category) {
            'conveyor_totebox' => 'bg-primary',
            'conveyor_paket' => 'bg-info',
            'lift_merah' => 'bg-danger',
            'lift_kuning' => 'bg-warning text-dark',
            'chute' => 'bg-secondary',
            'others' => 'bg-dark',
            default => 'bg-secondary',
        };
    }

    /**
     * Get status badge class
     */
    public function getStatusBadgeClass(): string
    {
        return match($this->status) {
            'pending' => 'bg-secondary',
            'received' => 'bg-info',
            'in_progress' => 'bg-primary',
            'completed' => 'bg-success',
            'failed' => 'bg-danger',
            'cancelled' => 'bg-dark',
            default => 'bg-secondary',
        };
    }

    /**
     * Get assigned user
     */
    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Get handler (admin who processed the ticket)
     */
    public function handler()
    {
        return $this->belongsTo(User::class, 'handled_by');
    }

    /**
     * Get assigned technicians (multiple)
     */
    public function technicians()
    {
        return $this->belongsToMany(User::class, 'corrective_maintenance_technicians', 'cm_request_id', 'user_id')
            ->withPivot(['shift_info', 'notified_at', 'acknowledged_at'])
            ->withTimestamps();
    }

    /**
     * Auto-assign technicians based on current shift
     *
     * @param Carbon|null $dateTime The datetime to check shift (defaults to now)
     * @return array Array of assigned user IDs
     */
    public function autoAssignTechnicians(?Carbon $dateTime = null): array
    {
        $service = new ShiftAssignmentService();
        $dateTime = $dateTime ?? Carbon::now();

        // Get users on duty
        $usersOnDuty = $service->getUsersOnDuty($dateTime);

        if ($usersOnDuty->isEmpty()) {
            return [];
        }

        // Get shift info
        $shiftInfo = $service->getShiftInfo($dateTime);
        $shiftName = $shiftInfo ? $shiftInfo['name'] : null;

        // Assign technicians
        $assignedIds = [];
        foreach ($usersOnDuty as $user) {
            // Check if not already assigned
            if (!$this->technicians()->where('user_id', $user->id)->exists()) {
                $this->technicians()->attach($user->id, [
                    'shift_info' => $shiftName,
                    'notified_at' => now(),
                ]);
                $assignedIds[] = $user->id;
            }
        }

        // Update ticket status to received and in_progress
        if (!empty($assignedIds)) {
            $this->status = 'in_progress';
            $this->received_at = now();
            $this->in_progress_at = now();
            $this->save();
        }

        return $assignedIds;
    }

    /**
     * Get technician names as comma-separated string
     */
    public function getTechnicianNamesAttribute(): string
    {
        return $this->technicians->pluck('name')->implode(', ') ?: '-';
    }

    /**
     * Mark as received and send email
     */
    public function markAsReceived($handledBy = null)
    {
        $this->status = 'received';
        $this->handled_by = $handledBy;
        $this->received_at = now();
        $this->received_email_sent_at = now();
        $this->save();
    }

    /**
     * Mark as in progress and send email
     */
    public function markAsInProgress($assignedTo, $handledBy = null)
    {
        $this->status = 'in_progress';
        $this->assigned_to = $assignedTo;
        $this->assigned_at = now();
        $this->started_at = now();
        $this->in_progress_at = now();
        $this->handled_by = $handledBy ?? $this->handled_by;
        $this->progress_email_sent_at = now();
        $this->save();
    }

    /**
     * Mark as completed
     */
    public function markAsCompleted($resolution, $handledBy = null)
    {
        $this->status = 'completed';
        $this->resolution = $resolution;
        $this->completed_at = now();
        $this->handled_by = $handledBy ?? $this->handled_by;
        $this->completed_email_sent_at = now();
        $this->save();
    }

    /**
     * Mark as failed
     */
    public function markAsFailed($resolution, $handledBy = null)
    {
        $this->status = 'failed';
        $this->resolution = $resolution;
        $this->completed_at = now();
        $this->handled_by = $handledBy ?? $this->handled_by;
        $this->completed_email_sent_at = now();
        $this->save();
    }

    /**
     * Check if ticket can be edited
     */
    public function canBeEdited(): bool
    {
        return in_array($this->status, ['pending', 'received']);
    }

    /**
     * Check if ticket is open (not completed/failed/cancelled)
     */
    public function isOpen(): bool
    {
        return in_array($this->status, ['pending', 'received', 'in_progress']);
    }

    /**
     * Get response time in hours
     */
    public function getResponseTimeHours(): ?float
    {
        if (!$this->received_email_sent_at) {
            return null;
        }
        return $this->created_at->diffInHours($this->received_email_sent_at);
    }

    /**
     * Get resolution time in hours
     */
    public function getResolutionTimeHours(): ?float
    {
        if (!$this->completed_at) {
            return null;
        }
        return $this->created_at->diffInHours($this->completed_at);
    }
}
