<?php

namespace App\Domain\Kitchen;

use App\Models\Booking;
use App\Models\BookingDay;
use App\Models\ClosureDay;
use Illuminate\Support\Carbon;

/**
 * Creates one booking_day (and its deliveries) per calendar day of a
 * booking's window, skipping full-day closures. Reused whenever the
 * window grows: start confirmation, pause, extension, and closure
 * shifting all call into this the same way.
 */
class BookingDayGenerator
{
    public function generateFor(Booking $booking): void
    {
        $this->createRange($booking, $booking->start_date->copy(), $booking->end_date->copy());
    }

    public function extendTail(Booking $booking, Carbon $from, Carbon $to): void
    {
        $this->createRange($booking, $from, $to);
    }

    private function createRange(Booking $booking, Carbon $from, Carbon $to): void
    {
        if ($from->gt($to)) {
            return;
        }

        $closureDates = ClosureDay::query()
            ->where('scope', 'all')
            ->whereBetween('date', [$from->toDateString(), $to->toDateString()])
            ->pluck('date')
            ->map(fn (Carbon $date) => $date->toDateString())
            ->all();

        for ($date = $from->copy(); $date->lte($to); $date->addDay()) {
            $status = in_array($date->toDateString(), $closureDates, true) ? 'skipped' : 'scheduled';

            $bookingDay = $booking->bookingDays()->firstOrCreate(
                ['date' => $date->toDateString()],
                ['menu_rotation_id' => $booking->variant->menu_rotation_id, 'status' => $status],
            );

            if ($status === 'scheduled' && $bookingDay->wasRecentlyCreated) {
                $this->createDeliveries($bookingDay, $booking);
            }
        }
    }

    private function createDeliveries(BookingDay $bookingDay, Booking $booking): void
    {
        $zoneId = $booking->address?->zone_id;

        $bookingDay->deliveries()->create(['run' => 'lunch', 'zone_id' => $zoneId]);

        if ($booking->meal_plan === 'lunch_dinner') {
            $bookingDay->deliveries()->create(['run' => 'dinner', 'zone_id' => $zoneId]);
        }
    }
}
