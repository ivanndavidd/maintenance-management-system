<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class BomItem extends TenantModels
{
    use HasFactory;

    protected $table = 'bom_items';

    protected $fillable = [
        'bom_id',
        'no',
        'material_code',
        'material_description',
        'qty',
        'unit',
        'price_unit',
        'price',
    ];

    protected $casts = [
        'qty'        => 'decimal:2',
        'price_unit' => 'decimal:2',
        'price'      => 'decimal:2',
    ];

    public function bom()
    {
        return $this->belongsTo(Bom::class, 'bom_id');
    }

    public function getFormattedPriceUnitAttribute(): string
    {
        if ($this->price_unit === null) return '-';
        return 'Rp ' . number_format($this->price_unit, 0, ',', '.');
    }

    public function getFormattedPriceAttribute(): string
    {
        if ($this->price === null) return '-';
        return 'Rp ' . number_format($this->price, 0, ',', '.');
    }
}
