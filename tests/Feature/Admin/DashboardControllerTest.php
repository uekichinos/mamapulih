<?php

use App\Domain\Booking\BookingPolicy;
use App\Domain\Settings\SettingsManager;
use App\Enums\BookingStatus;
use App\Models\User;

it('shows the admin dashboard with operational stats', function () {
    $owner = User::factory()->create();

    $active = expiryTestBooking(['status' => BookingStatus::Active, 'start_date' => now()->subDays(2), 'end_date' => now()->addDays(5)]);
    expiryTestBooking(['status' => BookingStatus::PendingApproval]);
    expiryTestBooking(['status' => BookingStatus::AwaitingPayment]);

    $response = $this->actingAs($owner)->get(route('admin.dashboard'))->assertOk();

    $response->assertInertia(fn ($page) => $page
        ->where('stats.active_mothers', 1)
        ->where('stats.awaiting_approval', 1)
        ->where('stats.pending_payments', 1)
    );

    expect($active->status)->toBe(BookingStatus::Active);
});

it('surfaces a buffer-capacity alert when a day exceeds regular capacity', function () {
    $owner = User::factory()->create();

    app(SettingsManager::class)->set('booking', [
        ...BookingPolicy::DEFAULTS,
        'regular_capacity' => 1,
        'buffer_capacity' => 1,
    ]);

    expiryTestBooking([
        'status' => BookingStatus::Active,
        'start_date' => now(),
        'end_date' => now()->addDays(3),
        'projected_period' => '['.now()->toDateString().','.now()->addDays(3)->toDateString().']',
        'slots_used' => 1,
    ]);
    expiryTestBooking([
        'status' => BookingStatus::Active,
        'start_date' => now(),
        'end_date' => now()->addDays(3),
        'projected_period' => '['.now()->toDateString().','.now()->addDays(3)->toDateString().']',
        'slots_used' => 1,
    ]);

    $response = $this->actingAs($owner)->get(route('admin.dashboard'))->assertOk();

    $response->assertInertia(fn ($page) => $page->has('bufferAlerts.0'));
});
