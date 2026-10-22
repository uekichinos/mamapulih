<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property array<string, mixed>|null $credentials
 * @property array<string, string>|null $instructions
 */
class PaymentProvider extends Model
{
    protected $fillable = ['driver', 'enabled', 'mode', 'credentials', 'display_name', 'instructions'];

    protected $hidden = ['credentials'];

    protected function casts(): array
    {
        return [
            'enabled' => 'boolean',
            'credentials' => 'encrypted:array',
            'instructions' => 'array',
        ];
    }
}
