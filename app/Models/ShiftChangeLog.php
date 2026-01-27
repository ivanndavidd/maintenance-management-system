<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShiftChangeLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'shift_assignment_id',
        'change_type',
        'original_user_id',
        'new_user_id',
        'reason',
        'effective_date',
        'changed_by',
    ];

    protected $casts = [
        'effective_date' => 'date',
    ];

    /**
     * Get the shift assignment
     */
    public function shiftAssignment()
    {
        return $this->belongsTo(ShiftAssignment::class);
    }

    /**
     * Get the original user
     */
    public function originalUser()
    {
        return $this->belongsTo(User::class, 'original_user_id');
    }

    /**
     * Get the new user (for replacements)
     */
    public function newUser()
    {
        return $this->belongsTo(User::class, 'new_user_id');
    }

    /**
     * Get who made the change
     */
    public function changedBy()
    {
        return $this->belongsTo(User::class, 'changed_by');
    }

    /**
     * Get formatted change description
     */
    public function getChangeDescriptionAttribute()
    {
        switch ($this->change_type) {
            case 'replacement':
                return sprintf(
                    '%s replaced by %s',
                    $this->originalUser->name ?? 'Unknown',
                    $this->newUser->name ?? 'Unknown'
                );
            case 'cancellation':
                return sprintf(
                    '%s shift cancelled',
                    $this->originalUser->name ?? 'Unknown'
                );
            case 'restoration':
                return sprintf(
                    'Cancelled shift restored to %s',
                    $this->newUser->name ?? 'Unknown'
                );
            default:
                return 'Unknown change';
        }
    }
}
