<?php

use App\Enums\BookingStatus;
use App\Models\Address;
use App\Models\Customer;
use App\Models\Package;
use App\Models\Zone;
use Illuminate\Support\Str;

function consentTestCustomerBooking(): array
{
    $package = Package::query()->create(['name' => ['en' => 'Standard', 'ms' => 'Standard']]);
    $normal = $package->variants()->create([
        'birth_type' => 'normal', 'duration_days' => 7, 'meal_plan' => 'lunch_dinner',
        'meals_per_day' => 2, 'price_sen' => 35000,
    ]);
    $package->variants()->create([
        'birth_type' => 'csec', 'duration_days' => 7, 'meal_plan' => 'lunch_dinner',
        'meals_per_day' => 2, 'price_sen' => 45000,
    ]);
    $customer = Customer::factory()->create();
    $zone = Zone::query()->create(['name' => 'Z', 'postcodes' => ['40000']]);
    $address = Address::query()->create(['customer_id' => $customer->id, 'zone_id' => $zone->id, 'line1' => 'x', 'postcode' => '40000']);

    $booking = $customer->bookings()->create([
        'ref_no' => 'BK-x', 'variant_id' => $normal->id, 'address_id' => $address->id,
        'edd' => now()->addDays(30), 'expected_birth_type' => 'normal', 'duration_days' => 7,
        'meal_plan' => 'lunch_dinner', 'slots_used' => 1, 'status' => BookingStatus::Reserved,
        'start_date' => now()->addDays(3),
        'price_sen' => 35000, 'daily_rate_sen' => 5000, 'policy_snapshot' => [],
        'projected_period' => '['.now()->addDays(30)->toDateString().','.now()->addDays(36)->toDateString().']',
    ]);

    return [$customer, $booking];
}

it('is reachable without logging in and agreeing applies the plan change', function () {
    [, $booking] = consentTestCustomerBooking();
    $csecVariant = $booking->variant->package->variants()->where('birth_type', 'csec')->first();

    $planChange = $booking->planChanges()->create([
        'from_variant_id' => $booking->variant_id,
        'to_variant_id' => $csecVariant->id,
        'effective_date' => $booking->start_date,
        'remaining_days' => $booking->duration_days,
        'amount_sen' => 10000,
        'status' => 'awaiting_consent',
    ]);

    $plainToken = Str::random(40);
    $planChange->consent()->create([
        'token_hash' => hash('sha256', $plainToken),
        'expires_at' => now()->addHours(48),
        'snapshot' => [],
    ]);

    $this->get(route('customer.consent.show', ['token' => $plainToken]))->assertOk();

    $this->post(route('customer.consent.store', ['token' => $plainToken]), ['decision' => 'agree'])
        ->assertRedirect(route('customer.consent.show', ['token' => $plainToken]));

    expect($planChange->refresh()->status)->toBe('agreed');
    expect($booking->refresh()->actual_birth_type)->toBe('csec');
});

it('shows a not-found state for an unknown token', function () {
    $this->get(route('customer.consent.show', ['token' => 'does-not-exist']))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->where('found', false));
});
