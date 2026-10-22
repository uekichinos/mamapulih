<?php

namespace App\Domain\Booking;

use App\Domain\Settings\SettingsManager;

/**
 * Typed read of the versioned "booking" settings key (deposit, start
 * notice, approval hold, capacity, twins) — shared by SettingsController
 * and the booking/capacity domain so both read the same defaults.
 */
class BookingPolicy
{
    public const DEFAULTS = [
        'deposit_type' => 'percentage',
        'deposit_amount' => 20,
        'balance_due_days_before_start' => 3,
        'start_notice_days' => 2,
        'approval_hold_hours' => 24,
        'payment_deadline_hours' => 48,
        'consent_expiry_hours' => 48,
        'regular_capacity' => 4,
        'buffer_capacity' => 2,
        'twins_slots' => 1,
    ];

    public function __construct(private SettingsManager $settings) {}

    /**
     * Merged over DEFAULTS so a setting saved before a new key existed
     * still gets that key's default, instead of silently missing it.
     *
     * @return array<string, mixed>
     */
    public function current(): array
    {
        return array_merge(self::DEFAULTS, $this->settings->get('booking', []));
    }

    public function regularCapacity(): int
    {
        return (int) $this->current()['regular_capacity'];
    }

    public function bufferCapacity(): int
    {
        return (int) $this->current()['buffer_capacity'];
    }

    public function twinsSlots(): int
    {
        return (int) $this->current()['twins_slots'];
    }

    public function approvalHoldHours(): int
    {
        return (int) $this->current()['approval_hold_hours'];
    }

    public function paymentDeadlineHours(): int
    {
        return (int) $this->current()['payment_deadline_hours'];
    }

    public function consentExpiryHours(): int
    {
        return (int) $this->current()['consent_expiry_hours'];
    }

    public function startNoticeDays(): int
    {
        return (int) $this->current()['start_notice_days'];
    }

    /**
     * The amount due as the first payment for the booking's chosen
     * option (full price, or just the deposit).
     */
    public function firstPaymentThresholdSen(int $priceSen, ?string $paymentOption): int
    {
        if ($paymentOption !== 'deposit') {
            return $priceSen;
        }

        $current = $this->current();

        if ($current['deposit_type'] === 'fixed') {
            return (int) $current['deposit_amount'] * 100;
        }

        return (int) round($priceSen * ((float) $current['deposit_amount'] / 100));
    }
}
