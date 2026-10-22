<?php

namespace App\Domain\Booking;

use App\Domain\Audit\AuditLogger;
use App\Domain\Kitchen\BookingDayGenerator;
use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\Pause;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class PauseService
{
    public function __construct(
        private BookingDayGenerator $generator,
        private BookingPolicy $policy,
        private AuditLogger $audit,
    ) {}

    public function pause(Booking $booking, int $days, string $reason, User $admin, bool $overrideLimit = false): Pause
    {
        $priorCount = $booking->pauses()->count();
        $limit = $this->policy->pauseLimitCount();
        $exceedsLimit = $priorCount >= $limit;

        if ($exceedsLimit && ! ($reason === 'medical' && $overrideLimit)) {
            throw new PauseLimitExceededException(__('booking.pause_limit_exceeded'));
        }

        return DB::transaction(function () use ($booking, $days, $reason, $admin, $exceedsLimit) {
            $from = now()->startOfDay();
            $to = $from->copy()->addDays($days - 1);

            $pause = $booking->pauses()->create([
                'from_date' => $from,
                'to_date' => $to,
                'reason' => $reason,
                'limit_overridden' => $exceedsLimit,
                'approved_by' => $admin->id,
            ]);

            $booking->bookingDays()
                ->whereBetween('date', [$from->toDateString(), $to->toDateString()])
                ->update(['status' => 'paused']);

            $oldEnd = $booking->end_date->copy();
            $newEnd = $oldEnd->copy()->addDays($days);

            $before = $booking->toArray();
            $booking->update(['end_date' => $newEnd, 'status' => BookingStatus::Paused]);

            $this->generator->extendTail($booking, $oldEnd->copy()->addDay(), $newEnd);

            $this->audit->record('booking.paused', $booking, $before, $booking->toArray(), $admin);

            return $pause;
        });
    }

    public function resume(Booking $booking, User $admin): Booking
    {
        abort_unless($booking->status === BookingStatus::Paused, 422, 'This booking is not paused.');

        $before = $booking->toArray();
        $booking->update(['status' => BookingStatus::Active]);
        $this->audit->record('booking.resumed', $booking, $before, $booking->toArray(), $admin);

        return $booking;
    }
}
