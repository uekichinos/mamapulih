<?php

namespace App\Domain\Capacity;

use RuntimeException;

/**
 * Thrown when a booking can no longer be accepted for its window — either
 * the initial check failed, or (rarely) another booking took the last
 * slot between the slot-check screen and the final submit.
 */
class CapacityFullException extends RuntimeException
{
    //
}
