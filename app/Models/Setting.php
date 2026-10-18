<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = ['key', 'value', 'version', 'effective_from'];

    protected function casts(): array
    {
        return [
            'value' => 'array',
            'effective_from' => 'datetime',
        ];
    }
}
