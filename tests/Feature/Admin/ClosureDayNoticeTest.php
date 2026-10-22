<?php

use App\Domain\Kitchen\BookingDayGenerator;
use App\Domain\Kitchen\ClosureNoticeMail;
use App\Enums\BookingStatus;
use App\Models\Address;
use App\Models\Booking;
use App\Models\ClosureDay;
use App\Models\Customer;
use App\Models\Package;
use App\Models\User;
use App\Models\Zone;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

it('shifts an active booking end date by one day and lets admin notify the customer', function () {
    Mail::fake();

    $owner = User::factory()->create();
    $booking = kitchenTestBooking('lunch', 7);
    app(BookingDayGenerator::class)->generateFor($booking);
    $originalEnd = $booking->end_date->copy();

    $this->actingAs($owner)->post(route('admin.closure-days.store'), [
        'date' => '2026-12-03',
        'scope' => 'all',
        'reason' => 'Public holiday',
    ])->assertRedirect();

    $booking->refresh();
    expect($booking->end_date->toDateString())->toBe($originalEnd->copy()->addDay()->toDateString());
    expect($booking->bookingDays()->where('date', '2026-12-03')->first()->status)->toBe('skipped');

    $closureDay = ClosureDay::query()->where('date', '2026-12-03')->first();

    $this->actingAs($owner)->post(route('admin.closure-days.notify', $closureDay))->assertRedirect();

    Mail::assertSent(ClosureNoticeMail::class, 1);
});

it('extends the projected period for a booking that has not started yet', function () {
    $owner = User::factory()->create();

    $package = Package::query()->create(['name' => ['en' => 'Standard', 'ms' => 'Standard']]);
    $variant = $package->variants()->create([
        'birth_type' => 'normal', 'duration_days' => 7, 'meal_plan' => 'lunch',
        'meals_per_day' => 1, 'price_sen' => 35000,
    ]);
    $customer = Customer::factory()->create();
    $zone = Zone::query()->create(['name' => 'Zone', 'postcodes' => ['40000']]);
    $address = Address::query()->create([
        'customer_id' => $customer->id, 'zone_id' => $zone->id, 'line1' => 'x', 'postcode' => '40000',
    ]);

    $booking = Booking::query()->create([
        'ref_no' => 'BK-'.uniqid(),
        'customer_id' => $customer->id,
        'variant_id' => $variant->id,
        'address_id' => $address->id,
        'edd' => '2026-12-10',
        'expected_birth_type' => 'normal',
        'duration_days' => 7,
        'meal_plan' => 'lunch',
        'slots_used' => 1,
        'status' => BookingStatus::Reserved,
        'price_sen' => 35000,
        'daily_rate_sen' => 5000,
        'policy_snapshot' => [],
        'projected_period' => '[2026-12-10,2026-12-16]',
    ]);

    $this->actingAs($owner)->post(route('admin.closure-days.store'), [
        'date' => '2026-12-12',
        'scope' => 'all',
    ]);

    // The stored "[2026-12-10,2026-12-16]" normalizes to an exclusive upper
    // bound of 2026-12-17; shifting by the one new closure day pushes it to 18.
    $raw = DB::selectOne('select upper(projected_period) as upper from bookings where id = ?', [$booking->id]);
    expect($raw->upper)->toBe('2026-12-18');
});
