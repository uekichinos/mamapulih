<?php

use App\Enums\BookingStatus;
use App\Models\Address;
use App\Models\Customer;
use App\Models\Package;
use App\Models\PaymentProvider;
use App\Models\Zone;

function startTestCustomerBooking(string $status = 'reserved'): array
{
    $package = Package::query()->create(['name' => ['en' => 'Standard', 'ms' => 'Standard']]);
    $variant = $package->variants()->create([
        'birth_type' => 'normal', 'duration_days' => 7, 'meal_plan' => 'lunch_dinner',
        'meals_per_day' => 2, 'price_sen' => 35000,
    ]);
    $customer = Customer::factory()->create();
    $zone = Zone::query()->create(['name' => 'Z', 'postcodes' => ['40000']]);
    $address = Address::query()->create(['customer_id' => $customer->id, 'zone_id' => $zone->id, 'line1' => 'x', 'postcode' => '40000']);

    $booking = $customer->bookings()->create([
        'ref_no' => 'BK-x', 'variant_id' => $variant->id, 'address_id' => $address->id,
        'edd' => now()->addDays(30), 'expected_birth_type' => 'normal', 'duration_days' => 7,
        'meal_plan' => 'lunch_dinner', 'slots_used' => 1, 'status' => $status,
        'price_sen' => 35000, 'daily_rate_sen' => 5000, 'policy_snapshot' => [],
        'projected_period' => '['.now()->addDays(30)->toDateString().','.now()->addDays(36)->toDateString().']',
    ]);

    return [$customer, $booking];
}

it('confirms start straight away when the actual birth type matches expectations', function () {
    [$customer, $booking] = startTestCustomerBooking();
    $provider = PaymentProvider::query()->where('driver', 'manual')->first();
    $booking->payments()->create([
        'provider_id' => $provider->id, 'type' => 'full', 'amount_sen' => $booking->price_sen,
        'payment_date' => now(), 'status' => 'verified',
    ]);

    $this->actingAs($customer, 'customers')->post(route('customer.start'), [
        'start_date' => now()->addDays(3)->toDateString(),
        'actual_birth_type' => 'normal',
    ])->assertRedirect(route('customer.my-booking'));

    expect($booking->refresh()->status)->toBe(BookingStatus::Active);
    expect($booking->end_date)->not->toBeNull();
});

it('raises a plan change instead of starting when the birth type differs', function () {
    [$customer, $booking] = startTestCustomerBooking();
    $booking->variant->package->variants()->create([
        'birth_type' => 'csec', 'duration_days' => 7, 'meal_plan' => 'lunch_dinner',
        'meals_per_day' => 2, 'price_sen' => 45000,
    ]);

    $this->actingAs($customer, 'customers')->post(route('customer.start'), [
        'start_date' => now()->addDays(3)->toDateString(),
        'actual_birth_type' => 'csec',
    ])->assertRedirect(route('customer.start'));

    expect($booking->refresh()->status)->toBe(BookingStatus::Reserved);
    expect($booking->planChanges()->where('status', 'awaiting_consent')->exists())->toBeTrue();
});

it('is not reachable unless the booking is Reserved', function () {
    [$customer] = startTestCustomerBooking('awaiting_payment');

    $this->actingAs($customer, 'customers')->get(route('customer.start'))->assertRedirect(route('customer.my-booking'));
});
