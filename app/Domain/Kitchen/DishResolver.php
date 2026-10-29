<?php

namespace App\Domain\Kitchen;

use App\Models\Dish;
use App\Models\RotationDay;

/**
 * Resolves which dish is served for a given rotation day/meal, substituting
 * for a customer's allergy/exclusion list where one applies. Shared by the
 * kitchen prep sheet (aggregated across bookings) and the customer-facing
 * schedule (per booking).
 */
class DishResolver
{
    /**
     * @param  array<int, string>  $exclusions
     * @return array{dish: Dish, substituted: bool}|null
     */
    public function resolve(int $menuRotationId, int $dayNo, string $meal, array $exclusions): ?array
    {
        $rotationDay = RotationDay::query()
            ->where('menu_rotation_id', $menuRotationId)
            ->where('day_no', $dayNo)
            ->where('meal', $meal)
            ->with('dish.substitute')
            ->first();

        if (! $rotationDay) {
            return null;
        }

        $dish = $rotationDay->dish;
        $substituted = false;

        if ($dish->excludedBy($exclusions) && $dish->substitute) {
            $dish = $dish->substitute;
            $substituted = true;
        }

        return ['dish' => $dish, 'substituted' => $substituted];
    }
}
