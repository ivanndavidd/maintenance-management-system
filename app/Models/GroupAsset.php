<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class GroupAsset extends TenantModels
{
    use HasFactory, SoftDeletes;

    protected $table = 'group_assets';

    protected $fillable = [
        'group_id',
        'group_name',
        'severity',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    const SEVERITY_HIGH   = 'high';
    const SEVERITY_MEDIUM = 'medium';
    const SEVERITY_LOW    = 'low';

    public static function severityLabels(): array
    {
        return [
            self::SEVERITY_HIGH   => 'High (No Backup / Main Line)',
            self::SEVERITY_MEDIUM => 'Medium (Bypass still possible, need manpower)',
            self::SEVERITY_LOW    => 'Low (Bypass still possible, don\'t need manpower)',
        ];
    }

    public static function severityBadgeColors(): array
    {
        return [
            self::SEVERITY_HIGH   => 'danger',
            self::SEVERITY_MEDIUM => 'warning',
            self::SEVERITY_LOW    => 'success',
        ];
    }

    public function getSeverityLabelAttribute(): string
    {
        return self::severityLabels()[$this->severity] ?? $this->severity;
    }

    public function getSeverityBadgeAttribute(): string
    {
        return self::severityBadgeColors()[$this->severity] ?? 'secondary';
    }

    /**
     * Auto-generate group_id format G + 2-digit sequence (G01, G52, ...)
     */
    public static function generateGroupId(): string
    {
        $latest = self::withTrashed()
            ->where('group_id', 'like', 'G%')
            ->orderByRaw('CAST(SUBSTRING(group_id, 2) AS UNSIGNED) DESC')
            ->first();

        if ($latest) {
            $sequence = (int) substr($latest->group_id, 1) + 1;
        } else {
            $sequence = 1;
        }

        return 'G' . str_pad($sequence, 2, '0', STR_PAD_LEFT);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->group_id)) {
                $model->group_id = self::generateGroupId();
            }
            if (auth()->check()) {
                $model->created_by = auth()->id();
            }
        });

        static::updating(function ($model) {
            if (auth()->check()) {
                $model->updated_by = auth()->id();
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
}
