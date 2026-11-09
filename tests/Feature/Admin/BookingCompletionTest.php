<?php

use App\Enums\BookingStatus;
use App\Models\AuditLog;
use App\Models\User;

it('lets an admin mark an active booking as completed early when a reason is given', function () {
    $owner = User::factory()->create();
    $booking = kitchenTestBooking();

    $this->actingAs($owner)
        ->post(route('admin.bookings.complete', $booking), ['reason' => 'Mother discharged confinement early.'])
        ->assertRedirect();

    $booking->refresh();
    expect($booking->status)->toBe(BookingStatus::Completed);

    $log = AuditLog::query()->where('action', 'booking.completed')->where('subject_id', $booking->id)->first();
    expect($log)->not->toBeNull();
    expect($log->after['completion_reason'])->toBe('Mother discharged confinement early.');
});

it('lets an admin mark a paused booking as completed when a reason is given', function () {
    $owner = User::factory()->create();
    $booking = kitchenTestBooking();
    $booking->update(['status' => BookingStatus::Paused]);

    $this->actingAs($owner)
        ->post(route('admin.bookings.complete', $booking), ['reason' => 'Customer requested early close-out.'])
        ->assertRedirect();

    expect($booking->refresh()->status)->toBe(BookingStatus::Completed);
});

it('refuses to complete a booking early without a reason', function () {
    $owner = User::factory()->create();
    $booking = kitchenTestBooking();

    $this->actingAs($owner)->post(route('admin.bookings.complete', $booking))->assertStatus(422);

    expect($booking->refresh()->status)->toBe(BookingStatus::Active);
});

it('lets an admin complete a booking with no reason once its end date has passed', function () {
    $owner = User::factory()->create();
    $booking = kitchenTestBooking();
    $booking->update(['end_date' => now()->subDay()]);

    $this->actingAs($owner)->post(route('admin.bookings.complete', $booking))->assertRedirect();

    expect($booking->refresh()->status)->toBe(BookingStatus::Completed);
});

it('refuses to complete a booking that is not active or paused', function () {
    $owner = User::factory()->create();
    $booking = kitchenTestBooking();
    $booking->update(['status' => BookingStatus::Cancelled]);

    $this->actingAs($owner)->post(route('admin.bookings.complete', $booking))->assertStatus(422);

    expect($booking->refresh()->status)->toBe(BookingStatus::Cancelled);
});
