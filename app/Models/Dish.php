<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property array<int, string> $tags
 */
class Dish extends Model
{
    protected $fillable = ['name', 'tags', 'substitute_dish_id', 'active'];

    protected function casts(): array
    {
        return [
            'name' => 'array',
            'tags' => 'array',
            'active' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<Dish, $this>
     */
    public function substitute(): BelongsTo
    {
        return $this->belongsTo(Dish::class, 'substitute_dish_id');
    }

    /**
     * @param  array<int, string>  $exclusions
     */
    public function excludedBy(array $exclusions): bool
    {
        return count(array_intersect($this->tags, $exclusions)) > 0;
    }
}
