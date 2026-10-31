<?php

use App\Enums\BookingStatus;
use App\Models\User;

it('lists pending bookings in the approval queue', function () {
    $owner = User::factory()->create();
    $booking = pendingBooking();

    $this->actingAs($owner)->get(route('admin.booking-approvals.index'))->assertOk();
    expect($booking->status)->toBe(BookingStatus::PendingApproval);
});

it('approves a pending booking', function () {
    $owner = User::factory()->create();
    $booking = pendingBooking();

    $this->actingAs($owner)
        ->post(route('admin.booking-approvals.approve', $booking))
        ->assertRedirect();

    expect($booking->refresh()->status)->toBe(BookingStatus::AwaitingPayment);
    expect($booking->approved_by)->toBe($owner->id);
});

it('rejects a pending booking with a reason', function () {
    $owner = User::factory()->create();
    $booking = pendingBooking();

    $this->actingAs($owner)
        ->post(route('admin.booking-approvals.reject', $booking), ['reason' => 'No capacity for that diet need'])
        ->assertRedirect();

    expect($booking->refresh()->status)->toBe(BookingStatus::Rejected);
    expect($booking->rejection_reason)->toBe('No capacity for that diet need');
});

it('blocks a kitchen user from the approval queue', function () {
    $kitchen = User::factory()->kitchen()->create();

    $this->actingAs($kitchen)->get(route('admin.booking-approvals.index'))->assertForbidden();
});
