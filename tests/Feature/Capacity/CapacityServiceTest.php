<?php

use App\Domain\Capacity\CapacityService;
use App\Enums\BookingStatus;
use App\Models\Address;
use App\Models\Booking;
use App\Models\ClosureDay;
use App\Models\Customer;
use App\Models\Package;
use App\Models\Variant;
use App\Models\Zone;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

function makeVariant(int $durationDays = 7): Variant
{
    $package = Package::query()->create(['name' => ['en' => 'Standard', 'ms' => 'Standard']]);

    return $package->variants()->create([
        'birth_type' => 'normal',
        'duration_days' => $durationDays,
        'meal_plan' => 'lunch_dinner',
        'meals_per_day' => 2,
        'price_sen' => $durationDays * 5000,
    ]);
}

function makeBooking(Variant $variant, string $edd, int $slots = 1, BookingStatus $status = BookingStatus::PendingApproval): Booking
{
    $customer = Customer::factory()->create();
    $zone = Zone::query()->create(['name' => 'Test Zone', 'postcodes' => ['40000']]);
    $address = Address::query()->create([
        'customer_id' => $customer->id,
        'zone_id' => $zone->id,
        'line1' => 'Test address',
        'postcode' => '40000',
    ]);

    $capacity = app(CapacityService::class);
    $start = Carbon::parse($edd);
    $period = $capacity->extendForClosures($start->copy(), $variant->duration_days);

    return Booking::query()->create([
        'ref_no' => 'BK-TEST-'.uniqid(),
        'customer_id' => $customer->id,
        'variant_id' => $variant->id,
        'address_id' => $address->id,
        'edd' => $start,
        'expected_birth_type' => 'normal',
        'duration_days' => $variant->duration_days,
        'meal_plan' => $variant->meal_plan,
        'slots_used' => $slots,
        'status' => $status,
        'price_sen' => $variant->price_sen,
        'daily_rate_sen' => $variant->dailyRateSen(),
        'policy_snapshot' => [],
        'projected_period' => "[{$period['start']->toDateString()},{$period['end']->toDateString()}]",
    ]);
}

it('extends the projected period by one day per closure in the window', function () {
    $capacity = app(CapacityService::class);
    $start = Carbon::parse('2026-12-01');

    ClosureDay::query()->create(['date' => '2026-12-03', 'scope' => 'all', 'source' => 'manual']);

    $period = $capacity->extendForClosures($start->copy(), 7);

    // 7 days from Dec 1 is Dec 7; one closure inside pushes it to Dec 8.
    expect($period['end']->toDateString())->toBe('2026-12-08');
});

it('extends again when the pushed-out tail itself contains a closure', function () {
    $capacity = app(CapacityService::class);
    $start = Carbon::parse('2026-12-01');

    ClosureDay::query()->create(['date' => '2026-12-03', 'scope' => 'all', 'source' => 'manual']);
    ClosureDay::query()->create(['date' => '2026-12-08', 'scope' => 'all', 'source' => 'manual']);

    $period = $capacity->extendForClosures($start->copy(), 7);

    expect($period['end']->toDateString())->toBe('2026-12-09');
});

it('counts committed slots per day and skips closure days entirely', function () {
    $variant = makeVariant(7);
    makeBooking($variant, '2026-12-01', slots: 2);

    ClosureDay::query()->create(['date' => '2026-12-03', 'scope' => 'all', 'source' => 'manual']);

    $capacity = app(CapacityService::class);
    $load = $capacity->loadForRange(Carbon::parse('2026-12-01'), Carbon::parse('2026-12-07'));

    expect($load)->not->toHaveKey('2026-12-03');
    expect($load['2026-12-01'])->toBe(2);
});

it('rejects a booking once regular capacity is reached for any day in its window', function () {
    $variant = makeVariant(7);
    makeBooking($variant, '2026-12-01', slots: 4);

    $capacity = app(CapacityService::class);
    $period = $capacity->extendForClosures(Carbon::parse('2026-12-01'), 7);

    expect($capacity->canAccept($period['start'], $period['end'], 1, regularCapacity: 4))->toBeFalse();
});

it('finds the next available start date once the chosen one is full', function () {
    $variant = makeVariant(7);
    makeBooking($variant, '2026-12-01', slots: 4);

    $capacity = app(CapacityService::class);

    $next = $capacity->nextAvailableStart(Carbon::parse('2026-12-01'), 7, 1, regularCapacity: 4);

    expect($next?->toDateString())->toBe('2026-12-08');
});

it('re-checks capacity inside the lock before accepting, so the last slot is only given out once', function () {
    // This proves the check-then-insert sequence is correct (one booking
    // taking the last slot makes the next check fail); it does not spin
    // up two real concurrent DB connections, so it cannot by itself prove
    // the advisory lock prevents a true race — that guarantee comes from
    // pg_advisory_xact_lock serializing concurrent transactions in
    // Postgres itself.
    $variant = makeVariant(7);
    makeBooking($variant, '2026-12-01', slots: 3);

    $capacity = app(CapacityService::class);

    $firstCheck = DB::transaction(function () use ($capacity, $variant) {
        $capacity->lock();
        $period = $capacity->extendForClosures(Carbon::parse('2026-12-01'), 7);
        $canAccept = $capacity->canAccept($period['start'], $period['end'], 1, regularCapacity: 4);

        if ($canAccept) {
            makeBooking($variant, '2026-12-01', slots: 1);
        }

        return $canAccept;
    });

    $secondCheck = DB::transaction(function () use ($capacity) {
        $capacity->lock();
        $period = $capacity->extendForClosures(Carbon::parse('2026-12-01'), 7);

        return $capacity->canAccept($period['start'], $period['end'], 1, regularCapacity: 4);
    });

    expect([$firstCheck, $secondCheck])->toBe([true, false]);
});
