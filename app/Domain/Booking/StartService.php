<?php

namespace App\Domain\Booking;

use App\Domain\Audit\AuditLogger;
use App\Domain\Capacity\CapacityService;
use App\Domain\Kitchen\BookingDayGenerator;
use App\Domain\Notifications\CustomerMailer;
use App\Domain\Notifications\StartConfirmedMail;
use App\Domain\Payments\PaymentService;
use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\PlanChange;
use Illuminate\Support\Carbon;

class StartService
{
    public function __construct(
        private CapacityService $capacity,
        private ConsentService $consents,
        private PaymentService $payments,
        private BookingDayGenerator $bookingDays,
        private CustomerMailer $mailer,
        private AuditLogger $audit,
    ) {}

    /**
     * Records the requested start date, then either confirms immediately
     * (birth type unchanged) or raises a consent (birth type changed —
     * the booking stays Reserved until that resolves).
     */
    public function request(Booking $booking, Carbon $startDate, string $actualBirthType): Booking|PlanChange
    {
        $booking->update(['start_date' => $startDate]);

        if ($actualBirthType === $booking->expected_birth_type) {
            return $this->confirm($booking);
        }

        return $this->consents->raiseAtStart($booking, $actualBirthType);
    }

    public function canConfirm(Booking $booking): bool
    {
        if (is_null($booking->start_date)) {
            return false;
        }

        if ($booking->planChanges()->where('status', 'awaiting_consent')->exists()) {
            return false;
        }

        return $this->payments->balanceDueSen($booking) === 0;
    }

    public function confirm(Booking $booking): Booking
    {
        abort_unless($this->canConfirm($booking), 422, 'This booking is not ready to start yet.');

        $period = $this->capacity->extendForClosures($booking->start_date->copy(), $booking->duration_days);

        $before = $booking->toArray();
        $booking->update(['end_date' => $period['end'], 'status' => BookingStatus::Active]);
        $this->audit->record('booking.started', $booking, $before, $booking->toArray());

        $this->bookingDays->generateFor($booking->refresh());

        $this->mailer->send($booking, fn () => new StartConfirmedMail($booking));

        return $booking;
    }
}
