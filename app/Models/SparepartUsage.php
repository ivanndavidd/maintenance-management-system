<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SparepartUsage extends TenantModels
{
    protected $table = 'sparepart_usages';

    protected $fillable = [
        'sparepart_id',
        'quantity_used',
        'used_at',
        'notes',
        'used_by',
    ];

    protected $casts = [
        'used_at' => 'date',
    ];

    public function sparepart()
    {
        return $this->belongsTo(Sparepart::class, 'sparepart_id');
    }

    public function usedByUser()
    {
        return $this->belongsTo(User::class, 'used_by');
    }
}
