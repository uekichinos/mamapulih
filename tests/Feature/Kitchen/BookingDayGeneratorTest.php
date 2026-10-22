<?php

use App\Domain\Kitchen\BookingDayGenerator;
use App\Enums\BookingStatus;
use App\Models\Address;
use App\Models\Booking;
use App\Models\ClosureDay;
use App\Models\Customer;
use App\Models\MenuRotation;
use App\Models\Package;
use App\Models\Zone;
use Illuminate\Support\Carbon;

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

it('generates one booking_day with two deliveries per day for lunch_dinner', function () {
    $booking = kitchenTestBooking('lunch_dinner', 7);

    app(BookingDayGenerator::class)->generateFor($booking);

    expect($booking->bookingDays()->count())->toBe(7);
    $day = $booking->bookingDays()->first();
    expect($day->status)->toBe('scheduled');
    expect($day->deliveries()->pluck('run')->sort()->values()->all())->toBe(['dinner', 'lunch']);
});

it('generates only a lunch delivery for lunch-only meal plans', function () {
    $booking = kitchenTestBooking('lunch', 7);

    app(BookingDayGenerator::class)->generateFor($booking);

    $day = $booking->bookingDays()->first();
    expect($day->deliveries()->pluck('run')->all())->toBe(['lunch']);
});

it('marks a closure date inside the window as skipped with no deliveries', function () {
    $booking = kitchenTestBooking('lunch', 7);
    ClosureDay::query()->create(['date' => '2026-12-03', 'scope' => 'all', 'source' => 'manual']);

    app(BookingDayGenerator::class)->generateFor($booking);

    $closed = $booking->bookingDays()->where('date', '2026-12-03')->first();
    expect($closed->status)->toBe('skipped');
    expect($closed->deliveries()->count())->toBe(0);
});

it('extends the tail with new scheduled days without duplicating existing ones', function () {
    $booking = kitchenTestBooking('lunch', 7);
    app(BookingDayGenerator::class)->generateFor($booking);

    app(BookingDayGenerator::class)->extendTail($booking, Carbon::parse('2026-12-08'), Carbon::parse('2026-12-09'));

    expect($booking->bookingDays()->count())->toBe(9);
});
