<?php

namespace App\Models;

class ToolUsageRequest extends TenantModels
{
    protected $fillable = [
        'request_number',
        'tool_id',
        'requested_by',
        'quantity_requested',
        'usage_date',
        'return_date',
        'purpose',
        'location',
        'notes',
        'status',
        'reviewed_by',
        'reviewed_at',
        'review_notes',
        'returned_at',
        'return_notes',
    ];

    protected $casts = [
        'usage_date'  => 'date',
        'return_date' => 'date',
        'reviewed_at' => 'datetime',
        'returned_at' => 'datetime',
    ];

    public function tool()
    {
        return $this->belongsTo(Tool::class);
    }

    public function requester()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public static function generateRequestNumber(): string
    {
        $prefix  = 'TUR';
        $date    = now()->format('Ymd');
        $pattern = $prefix . '-' . $date . '-%';
        $last    = self::where('request_number', 'like', $pattern)->orderByDesc('request_number')->first();
        $seq     = $last ? ((int) substr($last->request_number, -4)) + 1 : 1;
        return $prefix . '-' . $date . '-' . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }

    public function isConsumable(): bool
    {
        return false;
    }

    public function getStatusBadgeClass(): string
    {
        return match($this->status) {
            'pending'   => 'warning',
            'approved'  => 'success',
            'rejected'  => 'danger',
            'in_use'    => 'primary',
            'used'      => 'info',
            'returned'  => 'secondary',
            'cancelled' => 'dark',
            default     => 'secondary',
        };
    }

    public function getStatusLabel(): string
    {
        return match($this->status) {
            'pending'   => 'Pending Approval',
            'approved'  => 'Approved',
            'rejected'  => 'Rejected',
            'in_use'    => 'In Use',
            'used'      => 'Used',
            'returned'  => 'Returned',
            'cancelled' => 'Cancelled',
            default     => ucfirst($this->status),
        };
    }

    public function canBeCancelledBy(int $userId): bool
    {
        return $this->requested_by === $userId && $this->status === 'pending';
    }

    public function canBeMarkedReturned(): bool
    {
        return in_array($this->status, ['approved', 'in_use']);
    }
}
