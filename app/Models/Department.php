<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'code', 'location', 'description', 'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Relationships
    public function machines()
    {
        return $this->hasMany(Machine::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }
}