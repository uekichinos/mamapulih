<?php

use App\Enums\BookingStatus;
use App\Models\Address;
use App\Models\Booking;
use App\Models\Customer;
use App\Models\Package;
use App\Models\User;
use App\Models\Zone;

function adminPaymentTestBooking(): Booking
{
    $package = Package::query()->create(['name' => ['en' => 'Standard', 'ms' => 'Standard']]);
    $variant = $package->variants()->create([
        'birth_type' => 'normal', 'duration_days' => 7, 'meal_plan' => 'lunch_dinner',
        'meals_per_day' => 2, 'price_sen' => 35000,
    ]);
    $customer = Customer::factory()->create();
    $zone = Zone::query()->create(['name' => 'Z', 'postcodes' => ['40000']]);
    $address = Address::query()->create(['customer_id' => $customer->id, 'zone_id' => $zone->id, 'line1' => 'x', 'postcode' => '40000']);

    return $customer->bookings()->create([
        'ref_no' => 'BK-x', 'variant_id' => $variant->id, 'address_id' => $address->id,
        'edd' => now()->addDays(30), 'expected_birth_type' => 'normal', 'duration_days' => 7,
        'meal_plan' => 'lunch_dinner', 'slots_used' => 1, 'status' => BookingStatus::AwaitingPayment,
        'payment_option' => 'full',
        'price_sen' => 35000, 'daily_rate_sen' => 5000, 'policy_snapshot' => [],
        'projected_period' => '['.now()->addDays(30)->toDateString().','.now()->addDays(36)->toDateString().']',
    ]);
}

it('records a payment against a booking and reserves it once fully paid', function () {
    $owner = User::factory()->create();
    $booking = adminPaymentTestBooking();

    $this->actingAs($owner)->post(route('admin.bookings.payments.store', $booking), [
        'type' => 'full',
        'amount' => 350.00,
        'payment_date' => now()->toDateString(),
        'method' => 'Bank transfer',
        'reference' => 'TXN123',
    ])->assertRedirect();

    expect($booking->refresh()->status)->toBe(BookingStatus::Reserved);
    expect($booking->payments()->count())->toBe(1);
});

it('blocks a kitchen user from recording payments', function () {
    $kitchen = User::factory()->kitchen()->create();
    $booking = adminPaymentTestBooking();

    $this->actingAs($kitchen)->post(route('admin.bookings.payments.store', $booking), [
        'type' => 'full', 'amount' => 350.00, 'payment_date' => now()->toDateString(),
    ])->assertForbidden();
});
