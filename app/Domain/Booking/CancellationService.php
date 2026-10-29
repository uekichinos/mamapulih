<?php

namespace App\Domain\Booking;

use App\Domain\Audit\AuditLogger;
use App\Domain\Notifications\BookingCancelledByCustomerMail;
use App\Domain\Payments\PaymentService;
use App\Domain\Settings\SmtpSettings;
use App\Enums\BookingSensitiveFlag;
use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\BusinessProfile;
use App\Models\Refund;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use InvalidArgumentException;

class CancellationService
{
    public function __construct(
        private PaymentService $payments,
        private AuditLogger $audit,
        private SmtpSettings $smtp,
    ) {}

    /**
     * $type is 'normal', 'compassionate', or 'do_not_contact'. A refund is
     * only ever raised (as a pending record for the admin to action
     * through the existing markRefundPaid flow) when there's money to
     * give back; $overrideAmountSen lets the admin hand back more than
     * the computed figure, which the business flow always allows for a
     * compassionate cancellation. A null $admin means the customer
     * cancelled their own booking (see BookingPolicy's self-cancel
     * window): the business gets an internal notice instead, same
     * treatment as a new booking awaiting approval.
     */
    public function cancel(Booking $booking, ?User $admin, string $type, ?string $reason = null, ?int $overrideAmountSen = null): ?Refund
    {
        if (! in_array($type, ['normal', 'compassionate', 'do_not_contact'], true)) {
            throw new InvalidArgumentException("Unknown cancellation type [{$type}].");
        }

        abort_if($booking->status === BookingStatus::Cancelled, 422, 'This booking is already cancelled.');

        return DB::transaction(function () use ($booking, $admin, $type, $reason, $overrideAmountSen) {
            $before = $booking->toArray();

            $sensitiveFlag = match ($type) {
                'compassionate' => BookingSensitiveFlag::Compassionate,
                'do_not_contact' => BookingSensitiveFlag::DoNotContact,
                default => $booking->sensitive_flag,
            };

            $booking->update([
                'status' => BookingStatus::Cancelled,
                'sensitive_flag' => $sensitiveFlag,
                'sensitive_reason' => $reason,
            ]);

            if ($type === 'do_not_contact') {
                $booking->customer->update(['do_not_contact' => true]);
            }

            $amountSen = $overrideAmountSen ?? $this->refundAmountSen($booking, $type);

            $booking->bookingDays()
                ->where('status', 'scheduled')
                ->where('date', '>=', now()->toDateString())
                ->delete();

            $this->audit->record('booking.cancelled', $booking, $before, $booking->toArray(), $admin);

            if (is_null($admin)) {
                $this->notifyBusinessOfCustomerCancellation($booking);
            }

            if ($amountSen <= 0) {
                return null;
            }

            $refund = Refund::query()->create([
                'booking_id' => $booking->id,
                'reason' => $type,
                'amount_sen' => $amountSen,
                'status' => 'pending',
            ]);

            $this->audit->record('refund.created', $refund, after: $refund->toArray(), actor: $admin);

            return $refund;
        });
    }

    private function notifyBusinessOfCustomerCancellation(Booking $booking): void
    {
        $businessEmail = BusinessProfile::current()->email;

        if (blank($businessEmail)) {
            return;
        }

        $this->smtp->applyToRuntimeConfig();
        Mail::to($businessEmail)->send(new BookingCancelledByCustomerMail($booking));
    }

    private function refundAmountSen(Booking $booking, string $type): int
    {
        $paidSen = $this->payments->amountPaid($booking);

        if ($type === 'compassionate') {
            return $paidSen;
        }

        if (is_null($booking->start_date) || $booking->start_date->isFuture()) {
            return $paidSen;
        }

        $remainingDays = $booking->bookingDays()
            ->where('status', 'scheduled')
            ->where('date', '>=', now()->toDateString())
            ->count();

        return min($paidSen, $remainingDays * $booking->daily_rate_sen);
    }
}
