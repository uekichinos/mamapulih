<?php

use App\Enums\BookingStatus;
use App\Models\Address;
use App\Models\Customer;
use App\Models\Package;
use App\Models\User;
use App\Models\Zone;

function dashboardTestBooking(BookingStatus $status): array
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
        'ref_no' => 'BK-'.uniqid(), 'variant_id' => $variant->id, 'address_id' => $address->id,
        'edd' => now()->addDays(30), 'expected_birth_type' => 'normal', 'duration_days' => 7,
        'meal_plan' => 'lunch_dinner', 'slots_used' => 1, 'status' => $status,
        'price_sen' => 35000, 'daily_rate_sen' => 5000, 'policy_snapshot' => [],
        'projected_period' => '['.now()->addDays(30)->toDateString().','.now()->addDays(36)->toDateString().']',
    ]);

    return [$customer, $booking];
}

it('shows the no-booking state for a customer with no bookings', function () {
    $customer = Customer::factory()->create();

    $this->actingAs($customer, 'customers')
        ->get(route('customer.dashboard'))
        ->assertInertia(
            fn ($page) => $page
                ->component('Customer/Dashboard')
                ->has('bookings', 0),
        );
});

it('shows the current booking, balance due and pending consent', function () {
    [$customer, $booking] = dashboardTestBooking(BookingStatus::AwaitingPayment);

    $this->actingAs($customer, 'customers')
        ->get(route('customer.dashboard'))
        ->assertInertia(
            fn ($page) => $page
                ->component('Customer/Dashboard')
                ->where('bookings.0.id', $booking->id)
                ->where('bookings.0.balance_due', $booking->price_sen)
                ->where('bookings.0.pending_consent', false),
        );
});

it('excludes terminal-status bookings from the live list', function () {
    [$customer, $booking] = dashboardTestBooking(BookingStatus::Active);
    dashboardTestBooking(BookingStatus::Completed); // unrelated customer's booking — not counted

    $customer->bookings()->create([
        'ref_no' => 'BK-'.uniqid(), 'variant_id' => $booking->variant_id, 'address_id' => $booking->address_id,
        'edd' => now()->addDays(60), 'expected_birth_type' => 'normal', 'duration_days' => 7,
        'meal_plan' => 'lunch_dinner', 'slots_used' => 1, 'status' => BookingStatus::Completed,
        'price_sen' => 35000, 'daily_rate_sen' => 5000, 'policy_snapshot' => [],
        'projected_period' => '['.now()->addDays(60)->toDateString().','.now()->addDays(66)->toDateString().']',
    ]);

    $this->actingAs($customer, 'customers')
        ->get(route('customer.dashboard'))
        ->assertInertia(
            fn ($page) => $page
                ->has('bookings', 1)
                ->where('bookings.0.id', $booking->id),
        );
});

it('shows two simultaneously live bookings as separate groups', function () {
    [$customer, $first] = dashboardTestBooking(BookingStatus::AwaitingPayment);
    $first->update(['recipient_name' => 'Wife One']);
    [, $second] = dashboardTestBooking(BookingStatus::Active);
    $second->update(['customer_id' => $customer->id, 'recipient_name' => 'Wife Two']);

    $this->actingAs($customer, 'customers')
        ->get(route('customer.dashboard'))
        ->assertInertia(
            fn ($page) => $page
                ->has('bookings', 2)
                ->where('bookings.0.recipient_label', 'Wife Two')
                ->where('bookings.1.recipient_label', 'Wife One'),
        );
});

it('is not reachable by a staff account', function () {
    $owner = User::factory()->create();

    $this->actingAs($owner)->get(route('customer.dashboard'))->assertRedirect(route('customer.login'));
});
