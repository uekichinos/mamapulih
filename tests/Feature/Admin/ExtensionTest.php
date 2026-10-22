<?php

use App\Domain\Kitchen\BookingDayGenerator;
use App\Models\User;

it('extends an active booking, charges extra days, and generates the extra booking days', function () {
    $owner = User::factory()->create();
    $booking = kitchenTestBooking('lunch', 7);
    app(BookingDayGenerator::class)->generateFor($booking);

    $originalEnd = $booking->end_date->copy();
    $originalPrice = $booking->price_sen;
    $dailyRate = $booking->daily_rate_sen;

    $this->actingAs($owner)->post(route('admin.bookings.extend', $booking), [
        'days' => 3,
    ])->assertRedirect();

    $booking->refresh();
    expect($booking->duration_days)->toBe(10);
    expect($booking->end_date->toDateString())->toBe($originalEnd->copy()->addDays(3)->toDateString());
    expect($booking->price_sen)->toBe($originalPrice + $dailyRate * 3);
    expect($booking->bookingDays()->count())->toBe(10);
});
