<?php

namespace App\Enums;

/**
 * "Message safety rule": before any automatic message is sent, the
 * system checks the booking isn't marked compassionate, medical-paused,
 * or do-not-contact (docs — Sensitive cases).
 */
enum BookingSensitiveFlag: string
{
    case None = 'none';
    case Compassionate = 'compassionate';
    case Medical = 'medical';
    case DoNotContact = 'do_not_contact';

    public function blocksAutomaticMessages(): bool
    {
        return $this !== self::None;
    }

    public function label(): string
    {
        return match ($this) {
            self::None => 'None',
            self::Compassionate => 'Compassionate',
            self::Medical => 'Medical',
            self::DoNotContact => 'Do not contact',
        };
    }
}
