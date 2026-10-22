<?php

namespace App\Domain\Booking;

use RuntimeException;

/**
 * Thrown when a birth-type change at start has no matching active variant
 * (same package/duration/meal plan) for admin to have configured.
 */
class VariantUnavailableException extends RuntimeException
{
    //
}
