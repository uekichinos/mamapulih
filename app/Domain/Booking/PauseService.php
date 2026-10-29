<?php

namespace App\Domain\Booking;

use App\Domain\Audit\AuditLogger;
use App\Domain\Kitchen\BookingDayGenerator;
use App\Domain\Notifications\CustomerMailer;
use App\Domain\Notifications\PausedMail;
use App\Domain\Notifications\ResumedMail;
use App\Enums\BookingSensitiveFlag;
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
        private CustomerMailer $mailer,
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

        // The pause/resume notice itself still goes out even for a medical
        // pause (it's operational, not a "cheerful reminder") — but not if
        // the booking was already compassionate/do-not-contact beforehand.
        $alreadySilenced = $booking->sensitive_flag->blocksAutomaticMessages();

        return DB::transaction(function () use ($booking, $days, $reason, $admin, $exceedsLimit, $alreadySilenced) {
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

            $booking->update([
                'end_date' => $newEnd,
                'status' => BookingStatus::Paused,
                'sensitive_flag' => $reason === 'medical' && ! $alreadySilenced
                    ? BookingSensitiveFlag::Medical
                    : $booking->sensitive_flag,
            ]);

            $this->generator->extendTail($booking, $oldEnd->copy()->addDay(), $newEnd);

            $this->audit->record('booking.paused', $booking, $before, $booking->toArray(), $admin);

            if (! $alreadySilenced) {
                $this->mailer->sendRegardless($booking, fn () => new PausedMail($booking));
            }

            return $pause;
        });
    }

    public function resume(Booking $booking, User $admin): Booking
    {
        abort_unless($booking->status === BookingStatus::Paused, 422, 'This booking is not paused.');

        $wasMedical = $booking->sensitive_flag === BookingSensitiveFlag::Medical;

        $before = $booking->toArray();

        $booking->update([
            'status' => BookingStatus::Active,
            'sensitive_flag' => $wasMedical ? BookingSensitiveFlag::None : $booking->sensitive_flag,
        ]);

        $this->audit->record('booking.resumed', $booking, $before, $booking->toArray(), $admin);

        if (! $booking->sensitive_flag->blocksAutomaticMessages()) {
            $this->mailer->sendRegardless($booking, fn () => new ResumedMail($booking));
        }

        return $booking;
    }
}
