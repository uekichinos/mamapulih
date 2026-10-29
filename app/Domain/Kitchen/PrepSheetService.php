<?php

namespace App\Domain\Kitchen;

use App\Models\BookingDay;
use App\Models\Dish;
use Illuminate\Support\Carbon;

class PrepSheetService
{
    public function __construct(private DishResolver $dishes) {}

    /**
     * @return array<string, array<int, array{dish: Dish, count: int, substituted: int}>>
     */
    public function forDate(Carbon $date): array
    {
        $bookingDays = BookingDay::query()
            ->where('date', $date->toDateString())
            ->where('status', 'scheduled')
            ->with(['booking.customer.dietProfile'])
            ->get();

        $result = [];

        foreach ($bookingDays as $bookingDay) {
            $booking = $bookingDay->booking;

            if (! $booking->start_date || ! $bookingDay->menu_rotation_id) {
                continue;
            }

            $dayNo = ($booking->start_date->diffInDays($date) % 7) + 1;
            $dietProfile = $booking->customer->dietProfile;
            $exclusions = $dietProfile === null
                ? []
                : array_merge($dietProfile->allergies ?? [], $dietProfile->exclusions ?? []);

            $meals = $booking->meal_plan === 'lunch_dinner' ? ['lunch', 'dinner'] : ['lunch'];

            foreach ($meals as $meal) {
                $resolved = $this->dishes->resolve($bookingDay->menu_rotation_id, $dayNo, $meal, $exclusions);

                if (! $resolved) {
                    continue;
                }

                $dish = $resolved['dish'];

                $result[$meal][$dish->id] ??= ['dish' => $dish, 'count' => 0, 'substituted' => 0];
                $result[$meal][$dish->id]['count']++;

                if ($resolved['substituted']) {
                    $result[$meal][$dish->id]['substituted']++;
                }
            }
        }

        return $result;
    }
}
