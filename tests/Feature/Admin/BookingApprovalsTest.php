<?php

use App\Enums\BookingStatus;
use App\Models\Address;
use App\Models\Booking;
use App\Models\Customer;
use App\Models\Package;
use App\Models\User;
use App\Models\Zone;

function pendingBooking(): Booking
{
    $package = Package::query()->create(['name' => ['en' => 'Standard', 'ms' => 'Standard']]);
    $variant = $package->variants()->create([
        'birth_type' => 'normal',
        'duration_days' => 7,
        'meal_plan' => 'lunch_dinner',
        'meals_per_day' => 2,
        'price_sen' => 35000,
    ]);
    $customer = Customer::factory()->create();
    $zone = Zone::query()->create(['name' => 'Shah Alam', 'postcodes' => ['40000']]);
    $address = Address::query()->create([
        'customer_id' => $customer->id,
        'zone_id' => $zone->id,
        'line1' => 'Test',
        'postcode' => '40000',
    ]);

    return Booking::query()->create([
        'ref_no' => 'BK-TEST-'.uniqid(),
        'customer_id' => $customer->id,
        'variant_id' => $variant->id,
        'address_id' => $address->id,
        'edd' => now()->addDays(30),
        'expected_birth_type' => 'normal',
        'duration_days' => 7,
        'meal_plan' => 'lunch_dinner',
        'slots_used' => 1,
        'status' => BookingStatus::PendingApproval,
        'price_sen' => 35000,
        'daily_rate_sen' => 5000,
        'policy_snapshot' => [],
        'projected_period' => '['.now()->addDays(30)->toDateString().','.now()->addDays(36)->toDateString().']',
    ]);
}

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
