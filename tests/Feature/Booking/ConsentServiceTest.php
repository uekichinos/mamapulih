<?php

use App\Domain\Booking\ConsentService;
use App\Domain\Booking\VariantUnavailableException;
use App\Enums\BookingStatus;
use App\Models\Address;
use App\Models\Booking;
use App\Models\Consent;
use App\Models\Customer;
use App\Models\Package;
use App\Models\Zone;
use Illuminate\Http\Request;

function consentTestBooking(): Booking
{
    $package = Package::query()->create(['name' => ['en' => 'Standard', 'ms' => 'Standard']]);

    $normal = $package->variants()->create([
        'birth_type' => 'normal', 'duration_days' => 14, 'meal_plan' => 'lunch_dinner',
        'meals_per_day' => 2, 'price_sen' => 699 * 100,
    ]);

    $package->variants()->create([
        'birth_type' => 'csec', 'duration_days' => 14, 'meal_plan' => 'lunch_dinner',
        'meals_per_day' => 2, 'price_sen' => 899 * 100,
    ]);

    $customer = Customer::factory()->create();
    $zone = Zone::query()->create(['name' => 'Zone', 'postcodes' => ['40000']]);
    $address = Address::query()->create([
        'customer_id' => $customer->id, 'zone_id' => $zone->id, 'line1' => 'x', 'postcode' => '40000',
    ]);

    return Booking::query()->create([
        'ref_no' => 'BK-'.uniqid(),
        'customer_id' => $customer->id,
        'variant_id' => $normal->id,
        'address_id' => $address->id,
        'edd' => now()->addDays(30),
        'expected_birth_type' => 'normal',
        'duration_days' => 14,
        'meal_plan' => 'lunch_dinner',
        'slots_used' => 1,
        'status' => BookingStatus::Reserved,
        'start_date' => now()->addDays(30),
        'price_sen' => $normal->price_sen,
        'daily_rate_sen' => $normal->dailyRateSen(),
        'policy_snapshot' => [],
        'projected_period' => '['.now()->addDays(30)->toDateString().','.now()->addDays(44)->toDateString().']',
    ]);
}

it('raises a plan change with the full-duration daily-rate difference', function () {
    $booking = consentTestBooking();

    $planChange = app(ConsentService::class)->raiseAtStart($booking, 'csec');

    $expected = ($planChange->toVariant->dailyRateSen() - $booking->daily_rate_sen) * $booking->duration_days;

    expect($planChange->status)->toBe('awaiting_consent');
    expect($planChange->amount_sen)->toBe($expected);
    expect(Consent::query()->where('plan_change_id', $planChange->id)->exists())->toBeTrue();
});

it('throws when no matching variant exists for the actual birth type', function () {
    $booking = consentTestBooking();
    $booking->update(['duration_days' => 21]); // no 21-day csec variant exists

    app(ConsentService::class)->raiseAtStart($booking->fresh(), 'csec');
})->throws(VariantUnavailableException::class);

it('applies the new variant to the booking when the customer agrees', function () {
    $booking = consentTestBooking();
    $planChange = app(ConsentService::class)->raiseAtStart($booking, 'csec');
    $consent = $planChange->consent;

    app(ConsentService::class)->decide($consent, 'agree', Request::create('/'));

    $booking->refresh();
    expect($booking->actual_birth_type)->toBe('csec');
    expect($booking->price_sen)->toBe((699 * 100) + $planChange->amount_sen);
    expect($planChange->refresh()->status)->toBe('agreed');
});

it('creates a pending refund when the agreed change is a downgrade', function () {
    $booking = consentTestBooking();
    $csecVariant = $booking->variant->package->variants()->where('birth_type', 'csec')->first();

    $booking->update([
        'variant_id' => $csecVariant->id,
        'expected_birth_type' => 'csec',
        'daily_rate_sen' => $csecVariant->dailyRateSen(),
        'price_sen' => $csecVariant->price_sen,
    ]);

    $planChange = app(ConsentService::class)->raiseAtStart($booking->fresh(), 'normal');

    app(ConsentService::class)->decide($planChange->consent, 'agree', Request::create('/'));

    expect($planChange->amount_sen)->toBeLessThan(0);
    expect($booking->refunds()->where('status', 'pending')->exists())->toBeTrue();
});

it('leaves the booking unchanged when the customer declines', function () {
    $booking = consentTestBooking();
    $planChange = app(ConsentService::class)->raiseAtStart($booking, 'csec');
    $consent = $planChange->consent;

    app(ConsentService::class)->decide($consent, 'decline', Request::create('/'));

    $booking->refresh();
    expect($booking->actual_birth_type)->toBeNull();
    expect($planChange->refresh()->status)->toBe('declined');
});
