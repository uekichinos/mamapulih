<?php

namespace App\Console\Commands;

use App\Domain\Audit\AuditLogger;
use App\Domain\Booking\BookingPolicy;
use App\Domain\Notifications\ApprovalExpiredMail;
use App\Domain\Notifications\CustomerMailer;
use App\Domain\Notifications\ResumedMail;
use App\Enums\BookingSensitiveFlag;
use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\PlanChange;
use Illuminate\Console\Command;

class ExpireBookingsCommand extends Command
{
    protected $signature = 'mamapulih:expire-bookings';

    protected $description = 'Expire bookings past their approval-hold or payment deadline, auto-resume elapsed pauses, and expire plan changes whose consent link has expired';

    public function __construct(
        private BookingPolicy $policy,
        private CustomerMailer $mailer,
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

        $resumedPauses = Booking::query()
            ->where('status', BookingStatus::Paused)
            ->get()
            ->filter(fn (Booking $booking) => (bool) $booking->pauses()->latest('to_date')->first()?->to_date->isPast());

        foreach ($resumedPauses as $booking) {
            $wasMedical = $booking->sensitive_flag === BookingSensitiveFlag::Medical;

            $before = $booking->toArray();
            $booking->update([
                'status' => BookingStatus::Active,
                'sensitive_flag' => $wasMedical ? BookingSensitiveFlag::None : $booking->sensitive_flag,
            ]);
            $this->audit->record('booking.resumed.auto', $booking, $before, $booking->toArray());

            if (! $booking->sensitive_flag->blocksAutomaticMessages()) {
                $this->mailer->sendRegardless($booking, fn () => new ResumedMail($booking));
            }
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
            '%d approval(s), %d payment(s) expired; %d pause(s) auto-resumed; %d consent(s) expired.',
            $expiredApprovals->count(),
            $expiredPayments->count(),
            $resumedPauses->count(),
            $expiredConsents->count(),
        ));

        return self::SUCCESS;
    }

    private function expire(Booking $booking, string $action): void
    {
        $before = $booking->toArray();
        $booking->update(['status' => BookingStatus::Expired]);
        $this->audit->record($action, $booking, $before, $booking->toArray());

        $this->mailer->send($booking, fn () => new ApprovalExpiredMail($booking));
    }
}
