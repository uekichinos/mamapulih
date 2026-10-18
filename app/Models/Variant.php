<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Variant extends Model
{
    protected $fillable = [
        'package_id',
        'birth_type',
        'duration_days',
        'meal_plan',
        'meals_per_day',
        'menu_rotation_id',
        'included_items',
        'price_sen',
        'active',
    ];

    protected function casts(): array
    {
        return [
            'included_items' => 'array',
            'active' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<Package, $this>
     */
    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    /**
     * @return BelongsTo<MenuRotation, $this>
     */
    public function menuRotation(): BelongsTo
    {
        return $this->belongsTo(MenuRotation::class);
    }

    /**
     * Daily rate in sen, rounded to the nearest sen.
     */
    public function dailyRateSen(): int
    {
        return intdiv($this->price_sen + intdiv($this->duration_days, 2), $this->duration_days);
    }
}
