<?php

use App\Enums\BookingStatus;

it('expires a pending-approval booking past the approval hold deadline', function () {
    $booking = expiryTestBooking(['created_at' => now()->subHours(25)]);

    $this->artisan('mamapulih:expire-bookings')->assertSuccessful();

    expect($booking->refresh()->status)->toBe(BookingStatus::Expired);
});

it('does not expire a pending-approval booking still within the hold window', function () {
    $booking = expiryTestBooking(['created_at' => now()->subHours(1)]);

    $this->artisan('mamapulih:expire-bookings');

    expect($booking->refresh()->status)->toBe(BookingStatus::PendingApproval);
});

it('expires an awaiting-payment booking past its payment deadline', function () {
    $booking = expiryTestBooking([
        'status' => BookingStatus::AwaitingPayment,
        'payment_due_at' => now()->subHour(),
    ]);

    $this->artisan('mamapulih:expire-bookings');

    expect($booking->refresh()->status)->toBe(BookingStatus::Expired);
});

it('expires a plan change whose consent link has expired without a decision', function () {
    $booking = expiryTestBooking(['status' => BookingStatus::Reserved]);
    $planChange = $booking->planChanges()->create([
        'from_variant_id' => $booking->variant_id,
        'to_variant_id' => $booking->variant_id,
        'effective_date' => now(),
        'remaining_days' => 7,
        'amount_sen' => 1000,
        'status' => 'awaiting_consent',
    ]);
    $planChange->consent()->create([
        'token_hash' => hash('sha256', 'x'),
        'expires_at' => now()->subHour(),
        'snapshot' => [],
    ]);

    $this->artisan('mamapulih:expire-bookings');

    expect($planChange->refresh()->status)->toBe('expired');
    expect($booking->refresh()->status)->toBe(BookingStatus::Reserved);
});
