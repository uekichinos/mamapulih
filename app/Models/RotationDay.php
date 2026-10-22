<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RotationDay extends Model
{
    protected $fillable = ['menu_rotation_id', 'day_no', 'meal', 'dish_id'];

    /**
     * @return BelongsTo<MenuRotation, $this>
     */
    public function menuRotation(): BelongsTo
    {
        return $this->belongsTo(MenuRotation::class);
    }

    /**
     * @return BelongsTo<Dish, $this>
     */
    public function dish(): BelongsTo
    {
        return $this->belongsTo(Dish::class);
    }
}
