<?php

use App\Enums\BookingStatus;
use App\Models\Addon;
use App\Models\Booking;
use App\Models\Customer;
use App\Models\Package;
use App\Models\User;
use App\Models\Zone;

it('creates a booking for a customer on their behalf', function () {
    $owner = User::factory()->create();

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

    $response = $this->actingAs($owner)->post(route('admin.bookings.store'), [
        'customer' => ['name' => 'Siti Aminah', 'email' => 'siti@example.com', 'phone' => '0123456789'],
        'variant_id' => $variant->id,
        'expected_birth_type' => 'normal',
        'is_twins' => false,
        'edd' => now()->addDays(30)->toDateString(),
        'address' => ['line1' => 'No 1, Jalan Test', 'postcode' => '40000'],
        'diet' => ['allergies' => [], 'exclusions' => [], 'notes' => null],
        'addons' => [['addon_id' => $addon->id, 'qty' => 1]],
    ]);

    $response->assertRedirect();

    $customer = Customer::query()->where('email', 'siti@example.com')->first();
    expect($customer)->not->toBeNull();

    $booking = Booking::query()->where('customer_id', $customer->id)->first();
    expect($booking->status)->toBe(BookingStatus::PendingApproval);
    expect($booking->created_by)->toBe($owner->id);
    expect($booking->price_sen)->toBe(35000 + 3000);
});

it('reuses an existing customer by email instead of duplicating them', function () {
    $owner = User::factory()->create();
    $existing = Customer::factory()->create(['email' => 'siti@example.com']);

    $package = Package::query()->create(['name' => ['en' => 'Standard', 'ms' => 'Standard']]);
    $variant = $package->variants()->create([
        'birth_type' => 'normal',
        'duration_days' => 7,
        'meal_plan' => 'lunch_dinner',
        'meals_per_day' => 2,
        'price_sen' => 35000,
    ]);
    Zone::query()->create(['name' => 'Shah Alam', 'postcodes' => ['40000']]);

    $this->actingAs($owner)->post(route('admin.bookings.store'), [
        'customer' => ['name' => 'Siti Aminah', 'email' => 'siti@example.com'],
        'variant_id' => $variant->id,
        'expected_birth_type' => 'normal',
        'is_twins' => false,
        'edd' => now()->addDays(30)->toDateString(),
        'address' => ['line1' => 'No 1, Jalan Test', 'postcode' => '40000'],
        'diet' => [],
    ]);

    expect(Customer::query()->where('email', 'siti@example.com')->count())->toBe(1);
    expect(Booking::query()->where('customer_id', $existing->id)->exists())->toBeTrue();
});
