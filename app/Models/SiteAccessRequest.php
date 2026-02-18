<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SiteAccessRequest extends Model
{
    use HasFactory;

    const TYPE_SITE_ACCESS = 'site_access';
    const TYPE_DELETE_USER = 'delete_user';
    const TYPE_TOGGLE_STATUS = 'toggle_status';

    protected $fillable = [
        'requested_by',
        'target_user_id',
        'type',
        'requested_site_ids',
        'current_site_ids',
        'status',
        'reviewed_by',
        'reviewed_at',
        'review_note',
    ];

    protected $casts = [
        'requested_site_ids' => 'array',
        'current_site_ids' => 'array',
        'reviewed_at' => 'datetime',
    ];

    public function requester()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function targetUser()
    {
        return $this->belongsTo(User::class, 'target_user_id');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }
}
