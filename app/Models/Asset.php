<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Asset extends TenantModels
{
    use HasFactory, SoftDeletes;

    protected $table = 'assets_master';

    protected $fillable = [
        'equipment_id',
        'bom_id',
        'group_id',
        'asset_name',
        'status',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($asset) {
            if (auth()->check()) {
                $asset->created_by = auth()->id();
            }
        });

        static::updating(function ($asset) {
            if (auth()->check()) {
                $asset->updated_by = auth()->id();
            }
        });
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function group()
    {
        return $this->belongsTo(GroupAsset::class, 'group_id', 'group_id');
    }
}
