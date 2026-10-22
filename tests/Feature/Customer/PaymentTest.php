<?php

use App\Enums\BookingStatus;
use App\Models\Address;
use App\Models\Customer;
use App\Models\Package;
use App\Models\PaymentProvider;
use App\Models\Zone;

it('lets a customer choose full or deposit and see the manual bank details', function () {
    $provider = PaymentProvider::query()->where('driver', 'manual')->first();
    $provider->update([
        'credentials' => ['bank_name' => 'Maybank', 'account_name' => 'MamaPulih', 'account_number' => '1234'],
        'instructions' => ['en' => 'Transfer then upload proof', 'ms' => ''],
    ]);

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
        'meal_plan' => 'lunch_dinner', 'slots_used' => 1, 'status' => BookingStatus::AwaitingPayment,
        'price_sen' => 35000, 'daily_rate_sen' => 5000, 'policy_snapshot' => [],
        'projected_period' => '['.now()->addDays(30)->toDateString().','.now()->addDays(36)->toDateString().']',
    ]);

    $this->actingAs($customer, 'customers')->get(route('customer.payment'))->assertOk();

    $this->actingAs($customer, 'customers')
        ->post(route('customer.payment'), ['payment_option' => 'deposit'])
        ->assertRedirect(route('customer.payment'));

    expect($booking->refresh()->payment_option)->toBe('deposit');
    expect($booking->payment_due_at)->not->toBeNull();
});

it('redirects away from the payment page when there is nothing awaiting payment', function () {
    $customer = Customer::factory()->create();

    $this->actingAs($customer, 'customers')
        ->get(route('customer.payment'))
        ->assertRedirect(route('customer.my-booking'));
});
