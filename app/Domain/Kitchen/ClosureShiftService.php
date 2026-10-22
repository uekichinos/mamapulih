<?php

namespace App\Domain\Kitchen;

use App\Domain\Audit\AuditLogger;
use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\ClosureDay;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

/**
 * When a new full-day closure lands inside a booking's window, every
 * affected booking moves its end date out by exactly one day (per the
 * business rule: one closure day = one day pushed out).
 */
class ClosureShiftService
{
    public function __construct(
        private BookingDayGenerator $generator,
        private AuditLogger $audit,
    ) {}

    /**
     * @return Collection<int, Booking>
     */
    public function shiftForNewClosure(ClosureDay $closureDay): Collection
    {
        $date = $closureDay->date->toDateString();

        $notYetStarted = Booking::query()
            ->whereIn('status', [BookingStatus::PendingApproval, BookingStatus::AwaitingPayment, BookingStatus::Reserved])
            ->whereRaw('projected_period @> ?::date', [$date])
            ->get();

        foreach ($notYetStarted as $booking) {
            $this->shiftProjectedPeriod($booking);
        }

        $started = Booking::query()
            ->whereIn('status', [BookingStatus::Active, BookingStatus::Paused])
            ->whereNotNull('start_date')
            ->whereNotNull('end_date')
            ->where('start_date', '<=', $date)
            ->where('end_date', '>=', $date)
            ->get();

        foreach ($started as $booking) {
            $this->shiftActiveBooking($booking, $date);
        }

        return $notYetStarted->concat($started);
    }

    private function shiftProjectedPeriod(Booking $booking): void
    {
        $before = $booking->toArray();

        DB::statement(
            "UPDATE bookings SET projected_period = daterange(lower(projected_period), upper(projected_period) + 1, '[)') WHERE id = ?",
            [$booking->id],
        );

        $this->audit->record('booking.closure_shifted', $booking->refresh(), $before, $booking->refresh()->toArray());
    }

    private function shiftActiveBooking(Booking $booking, string $closureDate): void
    {
        $before = $booking->toArray();
        $oldEnd = $booking->end_date->copy();
        $newEnd = $oldEnd->copy()->addDay();

        $booking->update(['end_date' => $newEnd]);
        $booking->bookingDays()->where('date', $closureDate)->update(['status' => 'skipped']);

        $this->generator->extendTail($booking, $oldEnd->copy()->addDay(), $newEnd);

        $this->audit->record('booking.closure_shifted', $booking, $before, $booking->toArray());
    }
}
