<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MenuRotation extends Model
{
    protected $fillable = ['name'];

    protected function casts(): array
    {
        return [
            'name' => 'array',
        ];
    }

    /**
     * @return HasMany<RotationDay, $this>
     */
    public function rotationDays(): HasMany
    {
        return $this->hasMany(RotationDay::class);
    }

    /**
     * @return HasMany<Variant, $this>
     */
    public function variants(): HasMany
    {
        return $this->hasMany(Variant::class);
    }
}
