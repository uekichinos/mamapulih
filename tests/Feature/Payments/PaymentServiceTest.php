<?php

use App\Domain\Payments\PaymentService;
use App\Enums\BookingStatus;
use App\Models\Address;
use App\Models\Booking;
use App\Models\Customer;
use App\Models\Package;
use App\Models\User;
use App\Models\Zone;

function paymentsTestBooking(string $paymentOption = 'deposit', int $priceSen = 350000): Booking
{
    $package = Package::query()->create(['name' => ['en' => 'Standard', 'ms' => 'Standard']]);
    $variant = $package->variants()->create([
        'birth_type' => 'normal',
        'duration_days' => 14,
        'meal_plan' => 'lunch_dinner',
        'meals_per_day' => 2,
        'price_sen' => $priceSen,
    ]);
    $customer = Customer::factory()->create();
    $zone = Zone::query()->create(['name' => 'Zone', 'postcodes' => ['40000']]);
    $address = Address::query()->create([
        'customer_id' => $customer->id, 'zone_id' => $zone->id, 'line1' => 'x', 'postcode' => '40000',
    ]);

    return Booking::query()->create([
        'ref_no' => 'BK-'.uniqid(),
        'customer_id' => $customer->id,
        'variant_id' => $variant->id,
        'address_id' => $address->id,
        'edd' => now()->addDays(30),
        'expected_birth_type' => 'normal',
        'duration_days' => 14,
        'meal_plan' => 'lunch_dinner',
        'slots_used' => 1,
        'status' => BookingStatus::AwaitingPayment,
        'payment_option' => $paymentOption,
        'price_sen' => $priceSen,
        'daily_rate_sen' => $variant->dailyRateSen(),
        'policy_snapshot' => [],
        'projected_period' => '['.now()->addDays(30)->toDateString().','.now()->addDays(44)->toDateString().']',
    ]);
}

it('moves a booking to Reserved once the deposit threshold is met', function () {
    $booking = paymentsTestBooking('deposit', 350000);
    $owner = User::factory()->create();

    $service = app(PaymentService::class);
    $service->record($booking, $owner, [
        'type' => 'deposit', 'amount_sen' => 70000, 'payment_date' => now()->toDateString(),
    ]);

    expect($booking->refresh()->status)->toBe(BookingStatus::Reserved);
});

it('does not reserve a booking until the full-payment threshold is met', function () {
    $booking = paymentsTestBooking('full', 350000);
    $owner = User::factory()->create();

    $service = app(PaymentService::class);
    $service->record($booking, $owner, [
        'type' => 'full', 'amount_sen' => 100000, 'payment_date' => now()->toDateString(),
    ]);

    expect($booking->refresh()->status)->toBe(BookingStatus::AwaitingPayment);
    expect($service->balanceDueSen($booking))->toBe(250000);

    $service->record($booking, $owner, [
        'type' => 'full', 'amount_sen' => 250000, 'payment_date' => now()->toDateString(),
    ]);

    expect($booking->refresh()->status)->toBe(BookingStatus::Reserved);
    expect($service->balanceDueSen($booking))->toBe(0);
});

it('marks the latest agreed upgrade plan change as paid when a plan_change payment is recorded', function () {
    $booking = paymentsTestBooking('full', 350000);
    $owner = User::factory()->create();
    $booking->update(['status' => BookingStatus::Reserved]);

    $planChange = $booking->planChanges()->create([
        'from_variant_id' => $booking->variant_id,
        'to_variant_id' => $booking->variant_id,
        'effective_date' => now(),
        'remaining_days' => 14,
        'amount_sen' => 14280,
        'status' => 'agreed',
    ]);

    app(PaymentService::class)->record($booking, $owner, [
        'type' => 'plan_change', 'amount_sen' => 14280, 'payment_date' => now()->toDateString(),
    ]);

    expect($planChange->refresh()->status)->toBe('paid');
});
