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
        'asset_id',
        'equipment_id',
        'asset_name',
        'location',
        'equipment_type',
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

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($asset) {
            // Only auto-generate asset_id if not already set
            // This allows manual assignment during imports while still auto-generating for regular creates
            if (empty($asset->asset_id)) {
                $asset->asset_id = self::generateAssetId();
            }

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

    /**
     * Generate Asset ID with format: AST + YYYYMMDD + XXX
     * Example: AST20240611001
     */
    public static function generateAssetId()
    {
        $date = now()->format('Ymd'); // YYYYMMDD
        $prefix = 'AST' . $date;

        // Use database locking to prevent duplicate IDs during concurrent inserts
        return \DB::transaction(function () use ($prefix) {
            // Get the latest asset for today with lock
            $latest = self::where('asset_id', 'like', $prefix . '%')
                ->lockForUpdate()
                ->orderBy('asset_id', 'desc')
                ->first();

            if ($latest) {
                // Extract the sequence number and increment
                $sequence = (int) substr($latest->asset_id, -3);
                $newSequence = str_pad($sequence + 1, 3, '0', STR_PAD_LEFT);
            } else {
                // First asset of the day
                $newSequence = '001';
            }

            return $prefix . $newSequence;
        });
    }

    /**
     * Get the user who created this asset
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated this asset
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
