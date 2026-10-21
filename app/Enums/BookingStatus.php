<?php

namespace App\Enums;

enum BookingStatus: string
{
    case Waitlisted = 'waitlisted';
    case PendingApproval = 'pending_approval';
    case Rejected = 'rejected';
    case AwaitingPayment = 'awaiting_payment';
    case Expired = 'expired';
    case Reserved = 'reserved';
    case Active = 'active';
    case Paused = 'paused';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Waitlisted => 'Waitlisted',
            self::PendingApproval => 'Pending approval',
            self::Rejected => 'Rejected',
            self::AwaitingPayment => 'Awaiting payment',
            self::Expired => 'Expired',
            self::Reserved => 'Reserved',
            self::Active => 'Active',
            self::Paused => 'Paused',
            self::Completed => 'Completed',
            self::Cancelled => 'Cancelled',
        };
    }

    /**
     * Statuses that still occupy a slot for capacity purposes.
     *
     * @return array<int, self>
     */
    public static function occupyingCapacity(): array
    {
        return [self::PendingApproval, self::AwaitingPayment, self::Reserved, self::Active, self::Paused];
    }
}
