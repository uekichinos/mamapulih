<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Zone extends Model
{
    protected $fillable = [
        'name',
        'postcodes',
        'free_delivery',
        'transport_cost_per_trip_sen',
        'one_trip_allowed',
    ];

    protected function casts(): array
    {
        return [
            'postcodes' => 'array',
            'free_delivery' => 'boolean',
            'one_trip_allowed' => 'boolean',
        ];
    }
}
