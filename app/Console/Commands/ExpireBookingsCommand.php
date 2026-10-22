<?php

namespace App\Console\Commands;

use App\Domain\Audit\AuditLogger;
use App\Domain\Booking\BookingPolicy;
use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\PlanChange;
use Illuminate\Console\Command;

class ExpireBookingsCommand extends Command
{
    protected $signature = 'mamapulih:expire-bookings';

    protected $description = 'Expire bookings past their approval-hold or payment deadline, and plan changes whose consent link has expired';

    public function __construct(
        private BookingPolicy $policy,
        private AuditLogger $audit,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $approvalCutoff = now()->subHours($this->policy->approvalHoldHours());

        $expiredApprovals = Booking::query()
            ->where('status', BookingStatus::PendingApproval)
            ->where('created_at', '<', $approvalCutoff)
            ->get();

        foreach ($expiredApprovals as $booking) {
            $this->expire($booking, 'booking.expired.approval_hold');
        }

        $expiredPayments = Booking::query()
            ->where('status', BookingStatus::AwaitingPayment)
            ->whereNotNull('payment_due_at')
            ->where('payment_due_at', '<', now())
            ->get();

        foreach ($expiredPayments as $booking) {
            $this->expire($booking, 'booking.expired.payment_deadline');
        }

        $expiredConsents = PlanChange::query()
            ->where('status', 'awaiting_consent')
            ->whereHas('consent', fn ($q) => $q->whereNull('decision')->where('expires_at', '<', now()))
            ->get();

        foreach ($expiredConsents as $planChange) {
            $planChange->update(['status' => 'expired']);
            $this->audit->record('plan_change.expired', $planChange);
        }

        $this->components->info(sprintf(
            '%d approval(s), %d payment(s), %d consent(s) expired.',
            $expiredApprovals->count(),
            $expiredPayments->count(),
            $expiredConsents->count(),
        ));

        return self::SUCCESS;
    }

    private function expire(Booking $booking, string $action): void
    {
        $before = $booking->toArray();
        $booking->update(['status' => BookingStatus::Expired]);
        $this->audit->record($action, $booking, $before, $booking->toArray());
    }
}
