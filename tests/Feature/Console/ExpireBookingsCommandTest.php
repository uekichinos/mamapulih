<?php

use App\Enums\BookingStatus;
use App\Models\Address;
use App\Models\Booking;
use App\Models\Customer;
use App\Models\Package;
use App\Models\Zone;

function expiryTestBooking(array $overrides = []): Booking
{
    $package = Package::query()->create(['name' => ['en' => 'Standard', 'ms' => 'Standard']]);
    $variant = $package->variants()->create([
        'birth_type' => 'normal', 'duration_days' => 7, 'meal_plan' => 'lunch_dinner',
        'meals_per_day' => 2, 'price_sen' => 35000,
    ]);
    $customer = Customer::factory()->create();
    $zone = Zone::query()->create(['name' => 'Zone', 'postcodes' => ['40000']]);
    $address = Address::query()->create([
        'customer_id' => $customer->id, 'zone_id' => $zone->id, 'line1' => 'x', 'postcode' => '40000',
    ]);

    // created_at isn't mass-assignable, so apply it after creation.
    $createdAt = $overrides['created_at'] ?? null;
    unset($overrides['created_at']);

    $booking = Booking::query()->create(array_merge([
        'ref_no' => 'BK-'.uniqid(),
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
    ], $overrides));

    if ($createdAt) {
        $booking->created_at = $createdAt;
        $booking->save();
    }

    return $booking;
}

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
