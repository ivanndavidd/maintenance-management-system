<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Bom extends TenantModels
{
    use HasFactory, SoftDeletes;

    protected $table = 'boms';

    protected $fillable = [
        'bom_id',
        'description',
        'created_by',
        'updated_by',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
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

    public function items()
    {
        return $this->hasMany(BomItem::class, 'bom_id')->orderBy('no');
    }

    public function assets()
    {
        return $this->hasMany(Asset::class, 'bom_id', 'bom_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function getTotalPriceAttribute(): float
    {
        return $this->items->sum('price') ?? 0;
    }
}
