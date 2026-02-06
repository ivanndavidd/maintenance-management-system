<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Site extends Model
{
    /**
     * The database connection that should be used by the model.
     * Sites table is always in the central database.
     */
    protected $connection = 'central';

    protected $fillable = [
        'code',
        'name',
        'database_name',
        'description',
        'logo',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get active sites only
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get the database connection configuration for this site
     */
    public function getDatabaseConfig(): array
    {
        return [
            'driver' => 'mysql',
            'host' => config('database.connections.mysql.host'),
            'port' => config('database.connections.mysql.port'),
            'database' => $this->database_name,
            'username' => config('database.connections.mysql.username'),
            'password' => config('database.connections.mysql.password'),
            'charset' => config('database.connections.mysql.charset', 'utf8mb4'),
            'collation' => config('database.connections.mysql.collation', 'utf8mb4_unicode_ci'),
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
        ];
    }
}
