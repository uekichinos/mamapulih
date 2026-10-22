<?php

use App\Domain\Booking\PauseLimitExceededException;
use App\Domain\Booking\PauseService;
use App\Domain\Kitchen\BookingDayGenerator;
use App\Enums\BookingStatus;
use App\Models\User;

it('pauses a booking, shifts the end date, and marks the window as paused', function () {
    $booking = kitchenTestBooking('lunch', 7);
    $owner = User::factory()->create();
    $originalEnd = $booking->end_date->copy();

    app(BookingDayGenerator::class)->generateFor($booking);

    app(PauseService::class)->pause($booking, 3, 'normal', $owner);

    $booking->refresh();
    expect($booking->status)->toBe(BookingStatus::Paused);
    expect($booking->end_date->toDateString())->toBe($originalEnd->copy()->addDays(3)->toDateString());
    expect($booking->pauses()->count())->toBe(1);
});

it('resumes a paused booking back to Active', function () {
    $booking = kitchenTestBooking('lunch', 7);
    $owner = User::factory()->create();
    app(PauseService::class)->pause($booking, 2, 'normal', $owner);

    app(PauseService::class)->resume($booking->refresh(), $owner);

    expect($booking->refresh()->status)->toBe(BookingStatus::Active);
});

it('blocks a third normal pause once the limit is reached', function () {
    $booking = kitchenTestBooking('lunch', 7);
    $owner = User::factory()->create();
    $service = app(PauseService::class);

    $service->pause($booking, 1, 'normal', $owner);
    $service->resume($booking->refresh(), $owner);
    $service->pause($booking->refresh(), 1, 'normal', $owner);
    $service->resume($booking->refresh(), $owner);

    $service->pause($booking->refresh(), 1, 'normal', $owner);
})->throws(PauseLimitExceededException::class);

it('allows a medical pause past the limit with an explicit override', function () {
    $booking = kitchenTestBooking('lunch', 7);
    $owner = User::factory()->create();
    $service = app(PauseService::class);

    $service->pause($booking, 1, 'normal', $owner);
    $service->resume($booking->refresh(), $owner);
    $service->pause($booking->refresh(), 1, 'normal', $owner);
    $service->resume($booking->refresh(), $owner);

    $pause = $service->pause($booking->refresh(), 2, 'medical', $owner, overrideLimit: true);

    expect($pause->limit_overridden)->toBeTrue();
    expect($booking->refresh()->status)->toBe(BookingStatus::Paused);
});
