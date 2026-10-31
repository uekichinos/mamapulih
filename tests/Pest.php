<?php

use App\Enums\BookingStatus;
use App\Models\Address;
use App\Models\Booking;
use App\Models\Customer;
use App\Models\MenuRotation;
use App\Models\Package;
use App\Models\Zone;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind a different classes or traits.
|
*/

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

/*
|--------------------------------------------------------------------------
| Shared test fixtures
|--------------------------------------------------------------------------
|
| Functions used by more than one test file live here rather than in the
| file that happens to define them first — Pest's --parallel runner puts
| each test file in its own worker process, and a global function defined
| in one file isn't visible to a different file running in another worker.
|
*/

/**
 * A pending-approval booking, for admin booking-approval tests.
 */
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

/**
 * A booking with overridable attributes, for expiry-command tests.
 *
 * @param  array<string, mixed>  $overrides
 */
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

/**
 * An active booking with its own menu rotation, for kitchen/delivery/
 * dashboard/cancellation/pause tests that need a running package.
 */
function kitchenTestBooking(string $mealPlan = 'lunch_dinner', int $durationDays = 7): Booking
{
    $rotation = MenuRotation::query()->create(['name' => ['en' => 'Standard', 'ms' => 'Standard']]);
    $package = Package::query()->create(['name' => ['en' => 'Standard', 'ms' => 'Standard']]);
    $variant = $package->variants()->create([
        'birth_type' => 'normal', 'duration_days' => $durationDays, 'meal_plan' => $mealPlan,
        'meals_per_day' => 2, 'menu_rotation_id' => $rotation->id, 'price_sen' => 35000,
    ]);
    $customer = Customer::factory()->create();
    $zone = Zone::query()->create(['name' => 'Zone', 'postcodes' => ['40000']]);
    $address = Address::query()->create([
        'customer_id' => $customer->id, 'zone_id' => $zone->id, 'line1' => 'x', 'postcode' => '40000',
    ]);

    $start = Carbon::parse('2026-12-01');
    $end = $start->copy()->addDays($durationDays - 1);

    return Booking::query()->create([
        'ref_no' => 'BK-'.uniqid(),
        'customer_id' => $customer->id,
        'variant_id' => $variant->id,
        'address_id' => $address->id,
        'edd' => $start,
        'expected_birth_type' => 'normal',
        'duration_days' => $durationDays,
        'meal_plan' => $mealPlan,
        'slots_used' => 1,
        'status' => BookingStatus::Active,
        'start_date' => $start,
        'end_date' => $end,
        'price_sen' => $variant->price_sen,
        'daily_rate_sen' => $variant->dailyRateSen(),
        'policy_snapshot' => [],
        'projected_period' => "[{$start->toDateString()},{$end->toDateString()}]",
    ]);
}
