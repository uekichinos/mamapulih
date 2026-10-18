<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Addon extends Model
{
    protected $fillable = ['name', 'price_sen', 'per', 'active'];

    protected function casts(): array
    {
        return [
            'name' => 'array',
            'active' => 'boolean',
        ];
    }
}
