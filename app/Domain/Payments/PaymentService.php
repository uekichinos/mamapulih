<?php

namespace App\Domain\Payments;

use App\Domain\Audit\AuditLogger;
use App\Domain\Booking\BookingPolicy;
use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\PaymentProvider;
use App\Models\PlanChange;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class PaymentService
{
    public function __construct(
        private BookingPolicy $policy,
        private AuditLogger $audit,
        private ReceiptMailer $receipts,
    ) {}

    public function amountPaid(Booking $booking): int
    {
        return (int) $booking->payments()
            ->whereIn('type', ['full', 'deposit', 'balance'])
            ->where('status', 'verified')
            ->sum('amount_sen');
    }

    public function balanceDueSen(Booking $booking): int
    {
        return max(0, $booking->price_sen - $this->amountPaid($booking));
    }

    /**
     * @param  array{type: string, amount_sen: int, payment_date: string, method?: ?string, reference?: ?string, notes?: ?string}  $data
     */
    public function record(Booking $booking, User $admin, array $data): Payment
    {
        return DB::transaction(function () use ($booking, $admin, $data) {
            $provider = PaymentProvider::query()->where('driver', 'manual')->firstOrFail();

            $payment = Payment::query()->create([
                'booking_id' => $booking->id,
                'provider_id' => $provider->id,
                'type' => $data['type'],
                'amount_sen' => $data['amount_sen'],
                'payment_date' => $data['payment_date'],
                'method' => $data['method'] ?? null,
                'reference' => $data['reference'] ?? null,
                'notes' => $data['notes'] ?? null,
                'recorded_by' => $admin->id,
                'status' => 'verified',
            ]);

            $this->audit->record('payment.recorded', $payment, after: $payment->toArray(), actor: $admin);

            if (in_array($data['type'], ['full', 'deposit', 'balance'], true)) {
                $this->maybeReserve($booking);
            }

            if ($data['type'] === 'plan_change') {
                $this->markLatestUpgradePaid($booking, $admin);
            }

            return $payment;
        });
    }

    private function maybeReserve(Booking $booking): void
    {
        if ($booking->status !== BookingStatus::AwaitingPayment) {
            return;
        }

        $threshold = $this->policy->firstPaymentThresholdSen($booking->price_sen, $booking->payment_option);

        if ($this->amountPaid($booking) < $threshold) {
            return;
        }

        $before = $booking->toArray();
        $booking->update(['status' => BookingStatus::Reserved]);
        $this->audit->record('booking.reserved', $booking, $before, $booking->toArray());

        $this->receipts->send($booking);
    }

    private function markLatestUpgradePaid(Booking $booking, User $admin): void
    {
        $planChange = $booking->planChanges()
            ->where('status', 'agreed')
            ->where('amount_sen', '>', 0)
            ->latest()
            ->first();

        if ($planChange instanceof PlanChange) {
            $planChange->update(['status' => 'paid']);
            $this->audit->record('plan_change.paid', $planChange, actor: $admin);
        }
    }
}
