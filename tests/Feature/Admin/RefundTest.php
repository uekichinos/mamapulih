<?php

use App\Enums\BookingStatus;
use App\Models\Address;
use App\Models\Customer;
use App\Models\Package;
use App\Models\User;
use App\Models\Zone;
use Illuminate\Http\UploadedFile;

it('marks a pending refund as paid with an uploaded proof', function () {
    $owner = User::factory()->create();

    $package = Package::query()->create(['name' => ['en' => 'Standard', 'ms' => 'Standard']]);
    $variant = $package->variants()->create([
        'birth_type' => 'normal', 'duration_days' => 7, 'meal_plan' => 'lunch_dinner',
        'meals_per_day' => 2, 'price_sen' => 35000,
    ]);
    $customer = Customer::factory()->create();
    $zone = Zone::query()->create(['name' => 'Z', 'postcodes' => ['40000']]);
    $address = Address::query()->create(['customer_id' => $customer->id, 'zone_id' => $zone->id, 'line1' => 'x', 'postcode' => '40000']);

    $booking = $customer->bookings()->create([
        'ref_no' => 'BK-x', 'variant_id' => $variant->id, 'address_id' => $address->id,
        'edd' => now()->addDays(30), 'expected_birth_type' => 'normal', 'duration_days' => 7,
        'meal_plan' => 'lunch_dinner', 'slots_used' => 1, 'status' => BookingStatus::Reserved,
        'price_sen' => 35000, 'daily_rate_sen' => 5000, 'policy_snapshot' => [],
        'projected_period' => '['.now()->addDays(30)->toDateString().','.now()->addDays(36)->toDateString().']',
    ]);

    $refund = $booking->refunds()->create([
        'reason' => 'Birth type change downgrade', 'amount_sen' => 10000, 'status' => 'pending',
    ]);

    $this->actingAs($owner)->post(route('admin.bookings.refunds.mark-paid', [$booking, $refund]), [
        'method' => 'Bank transfer',
        'reference' => 'REF-1',
        'proof' => UploadedFile::fake()->image('proof.jpg'),
    ])->assertRedirect();

    expect($refund->refresh()->status)->toBe('paid');
    expect($refund->proof_path)->not->toBeNull();
});
