<?php

namespace App\Domain\Booking;

use RuntimeException;

/**
 * Thrown when a booking has already used its pause allowance and the
 * request isn't a medical-override case.
 */
class PauseLimitExceededException extends RuntimeException
{
    //
}
