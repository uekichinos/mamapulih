<?php

use App\Models\User;

it('lists bookings for the owner', function () {
    $owner = User::factory()->create();
    $booking = pendingBooking();

    $this->actingAs($owner)->get(route('admin.bookings.index'))->assertOk();
    $this->actingAs($owner)->get(route('admin.bookings.show', $booking))->assertOk();
});

it('shows the admin-created booking form', function () {
    $owner = User::factory()->create();

    $this->actingAs($owner)->get(route('admin.bookings.create'))->assertOk();
});
