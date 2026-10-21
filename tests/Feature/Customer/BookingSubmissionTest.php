<?php

use App\Enums\BookingStatus;
use App\Models\Addon;
use App\Models\Booking;
use App\Models\Customer;
use App\Models\Package;
use App\Models\Zone;

function bookingCatalog(): array
{
    $package = Package::query()->create(['name' => ['en' => 'Standard', 'ms' => 'Standard']]);

    $variant = $package->variants()->create([
        'birth_type' => 'normal',
        'duration_days' => 7,
        'meal_plan' => 'lunch_dinner',
        'meals_per_day' => 2,
        'price_sen' => 35000,
    ]);

    $addon = Addon::query()->create([
        'name' => ['en' => 'Herbal drink', 'ms' => 'Air herba'],
        'price_sen' => 3000,
        'per' => 'booking',
    ]);

    Zone::query()->create(['name' => 'Shah Alam', 'postcodes' => ['40000']]);

    return [$variant, $addon];
}

it('walks a customer through build, slot check, details and review to a submitted booking', function () {
    [$variant, $addon] = bookingCatalog();
    $customer = Customer::factory()->create();

    $this->actingAs($customer, 'customers')
        ->post(route('customer.booking.build'), [
            'variant_id' => $variant->id,
            'expected_birth_type' => 'normal',
            'is_twins' => false,
            'addons' => [['addon_id' => $addon->id, 'qty' => 1]],
        ])
        ->assertRedirect(route('customer.booking.slot'));

    $this->actingAs($customer, 'customers')
        ->post(route('customer.booking.slot'), ['edd' => now()->addDays(30)->toDateString()])
        ->assertRedirect(route('customer.booking.details'));

    $this->actingAs($customer, 'customers')
        ->post(route('customer.booking.details'), [
            'address' => ['line1' => 'No 1, Jalan Test', 'postcode' => '40000'],
            'diet' => ['allergies' => ['seafood'], 'exclusions' => [], 'notes' => null],
        ])
        ->assertRedirect(route('customer.booking.review'));

    $response = $this->actingAs($customer, 'customers')->post(route('customer.booking.submit'));

    $response->assertRedirect(route('customer.my-booking'));

    $booking = Booking::query()->where('customer_id', $customer->id)->first();

    expect($booking)->not->toBeNull();
    expect($booking->status)->toBe(BookingStatus::PendingApproval);
    expect($booking->price_sen)->toBe(35000 + 3000);
});

it('refuses to submit when the draft is incomplete', function () {
    $customer = Customer::factory()->create();

    $this->actingAs($customer, 'customers')
        ->post(route('customer.booking.submit'))
        ->assertRedirect(route('customer.booking.build'));
});

it('rejects an address outside any delivery zone', function () {
    [$variant] = bookingCatalog();
    $customer = Customer::factory()->create();

    $this->actingAs($customer, 'customers')->post(route('customer.booking.build'), [
        'variant_id' => $variant->id,
        'expected_birth_type' => 'normal',
        'is_twins' => false,
    ]);

    $this->actingAs($customer, 'customers')->post(route('customer.booking.slot'), [
        'edd' => now()->addDays(30)->toDateString(),
    ]);

    $this->actingAs($customer, 'customers')->post(route('customer.booking.details'), [
        'address' => ['line1' => 'No 1, Jalan Test', 'postcode' => '99999'],
        'diet' => [],
    ]);

    $this->actingAs($customer, 'customers')
        ->post(route('customer.booking.submit'))
        ->assertSessionHasErrors('address.postcode');
});

it('shows the slot as full and suggests the next available date once regular capacity is reached', function () {
    [$variant] = bookingCatalog();

    foreach (range(1, 4) as $i) {
        $owner = Customer::factory()->create();
        $this->actingAs($owner, 'customers')->post(route('customer.booking.build'), [
            'variant_id' => $variant->id,
            'expected_birth_type' => 'normal',
            'is_twins' => false,
        ]);
        $this->actingAs($owner, 'customers')->post(route('customer.booking.slot'), [
            'edd' => '2026-12-01',
        ]);
        $this->actingAs($owner, 'customers')->post(route('customer.booking.details'), [
            'address' => ['line1' => 'No 1, Jalan Test', 'postcode' => '40000'],
            'diet' => [],
        ]);
        $this->actingAs($owner, 'customers')->post(route('customer.booking.submit'));
    }

    $customer = Customer::factory()->create();
    $this->actingAs($customer, 'customers')->post(route('customer.booking.build'), [
        'variant_id' => $variant->id,
        'expected_birth_type' => 'normal',
        'is_twins' => false,
    ]);

    $response = $this->actingAs($customer, 'customers')->post(route('customer.booking.slot'), [
        'edd' => '2026-12-01',
    ]);

    $response->assertInertia(fn ($page) => $page
        ->where('full', true)
        ->where('nextAvailable', '2026-12-08'));
});
