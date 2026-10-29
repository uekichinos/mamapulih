<?php

use App\Domain\Payments\PaymentService;
use App\Enums\BookingStatus;
use App\Models\Address;
use App\Models\Customer;
use App\Models\Package;
use App\Models\PaymentProvider;
use App\Models\User;
use App\Models\Zone;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

function customerPaymentTestBooking(BookingStatus $status = BookingStatus::AwaitingPayment)
{
    $package = Package::query()->create(['name' => ['en' => 'Standard', 'ms' => 'Standard']]);
    $variant = $package->variants()->create([
        'birth_type' => 'normal', 'duration_days' => 7, 'meal_plan' => 'lunch_dinner',
        'meals_per_day' => 2, 'price_sen' => 35000,
    ]);
    $customer = Customer::factory()->create();
    $zone = Zone::query()->create(['name' => 'Z', 'postcodes' => ['40000']]);
    $address = Address::query()->create(['customer_id' => $customer->id, 'zone_id' => $zone->id, 'line1' => 'x', 'postcode' => '40000']);

    $booking = $customer->bookings()->create([
        'ref_no' => 'BK-'.uniqid(), 'variant_id' => $variant->id, 'address_id' => $address->id,
        'edd' => now()->addDays(30), 'expected_birth_type' => 'normal', 'duration_days' => 7,
        'meal_plan' => 'lunch_dinner', 'slots_used' => 1, 'status' => $status,
        'price_sen' => 35000, 'daily_rate_sen' => 5000, 'policy_snapshot' => [],
        'projected_period' => '['.now()->addDays(30)->toDateString().','.now()->addDays(36)->toDateString().']',
    ]);

    return [$customer, $booking];
}

it('lets a customer choose full or deposit and see the manual bank details', function () {
    $provider = PaymentProvider::query()->where('driver', 'manual')->first();
    $provider->update([
        'credentials' => ['bank_name' => 'Maybank', 'account_name' => 'MamaPulih', 'account_number' => '1234'],
        'instructions' => ['en' => 'Transfer then upload proof', 'ms' => ''],
    ]);

    [$customer, $booking] = customerPaymentTestBooking();

    $this->actingAs($customer, 'customers')->get(route('customer.payment'))->assertOk();

    $this->actingAs($customer, 'customers')
        ->post(route('customer.payment'), ['payment_option' => 'deposit'])
        ->assertRedirect(route('customer.payment'));

    expect($booking->refresh()->payment_option)->toBe('deposit');
    expect($booking->payment_due_at)->not->toBeNull();
});

it('redirects away from the payment page when there is nothing awaiting payment', function () {
    $customer = Customer::factory()->create();

    $this->actingAs($customer, 'customers')
        ->get(route('customer.payment'))
        ->assertRedirect(route('customer.my-booking'));
});

it('lets a customer upload a payment receipt', function () {
    Storage::fake('local');
    [$customer, $booking] = customerPaymentTestBooking();

    $this->actingAs($customer, 'customers')
        ->post(route('customer.payment.receipt'), [
            'receipt' => UploadedFile::fake()->create('receipt.pdf', 100, 'application/pdf'),
        ])
        ->assertRedirect(route('customer.payment'));

    expect($booking->paymentReceipts()->count())->toBe(1);
    $receipt = $booking->paymentReceipts()->first();
    expect($receipt->original_name)->toBe('receipt.pdf');
    Storage::disk('local')->assertExists($receipt->path);
});

it('rejects a payment receipt of an unsupported file type', function () {
    Storage::fake('local');
    [$customer, $booking] = customerPaymentTestBooking();

    $this->actingAs($customer, 'customers')
        ->post(route('customer.payment.receipt'), [
            'receipt' => UploadedFile::fake()->create('receipt.exe', 100, 'application/octet-stream'),
        ])
        ->assertInvalid(['receipt']);

    expect($booking->paymentReceipts()->count())->toBe(0);
});

it('refuses to accept a receipt once there is nothing left to pay', function () {
    [$customer, $booking] = customerPaymentTestBooking(BookingStatus::Reserved);

    PaymentProvider::query()->firstOrCreate(['driver' => 'manual'], ['display_name' => 'Manual', 'enabled' => true]);
    app(PaymentService::class)->record($booking, User::factory()->create(), [
        'type' => 'full',
        'amount_sen' => $booking->price_sen,
        'payment_date' => now()->toDateString(),
    ]);

    $this->actingAs($customer, 'customers')
        ->post(route('customer.payment.receipt'), [
            'receipt' => UploadedFile::fake()->create('receipt.pdf', 100, 'application/pdf'),
        ])
        ->assertRedirect(route('customer.my-booking'));
});

it('shows the balance-payment page for a reserved booking with a balance still due', function () {
    [$customer] = customerPaymentTestBooking(BookingStatus::Reserved);

    $this->actingAs($customer, 'customers')
        ->get(route('customer.payment'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->where('balanceDueSen', 35000));
});

it('lets a customer upload a receipt for the remaining balance while reserved', function () {
    Storage::fake('local');
    [$customer, $booking] = customerPaymentTestBooking(BookingStatus::Reserved);

    $this->actingAs($customer, 'customers')
        ->post(route('customer.payment.receipt'), [
            'receipt' => UploadedFile::fake()->create('receipt.pdf', 100, 'application/pdf'),
        ])
        ->assertRedirect(route('customer.payment'));

    expect($booking->paymentReceipts()->count())->toBe(1);
});
