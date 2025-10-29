<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Part extends Model
{
    use HasFactory;

    protected $fillable = [
        'code', 'name', 'description', 'unit', 'stock_quantity',
        'minimum_stock', 'unit_cost', 'supplier', 'location', 'image', 'is_active'
    ];

    protected $casts = [
        'unit_cost' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    // Relationships
    public function workReports()
    {
        return $this->belongsToMany(WorkReport::class, 'report_parts')
                    ->withPivot('quantity', 'cost')
                    ->withTimestamps();
    }

    // Scopes
    public function scopeLowStock($query)
    {
        return $query->whereColumn('stock_quantity', '<=', 'minimum_stock');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Check if low stock
    public function isLowStock()
    {
        return $this->stock_quantity <= $this->minimum_stock;
    }
}