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
        'regular_capacity' => 4,
        'buffer_capacity' => 2,
        'twins_slots' => 1,
    ];

    public function __construct(private SettingsManager $settings) {}

    /**
     * @return array<string, mixed>
     */
    public function current(): array
    {
        return $this->settings->get('booking', self::DEFAULTS);
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
}
