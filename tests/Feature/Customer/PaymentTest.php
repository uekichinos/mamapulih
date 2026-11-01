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
        ->post(route('customer.payment'), ['booking_id' => $booking->id, 'payment_option' => 'deposit'])
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
            'booking_id' => $booking->id,
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
            'booking_id' => $booking->id,
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
            'booking_id' => $booking->id,
            'receipt' => UploadedFile::fake()->create('receipt.pdf', 100, 'application/pdf'),
        ])
        ->assertRedirect(route('customer.my-booking'));
});

it('shows the balance-payment page for a reserved booking with a balance still due', function () {
    [$customer] = customerPaymentTestBooking(BookingStatus::Reserved);

    $this->actingAs($customer, 'customers')
        ->get(route('customer.payment'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->where('payableBookings.0.balance_due_sen', 35000));
});

it('lets a customer upload a receipt for the remaining balance while reserved', function () {
    Storage::fake('local');
    [$customer, $booking] = customerPaymentTestBooking(BookingStatus::Reserved);

    $this->actingAs($customer, 'customers')
        ->post(route('customer.payment.receipt'), [
            'booking_id' => $booking->id,
            'receipt' => UploadedFile::fake()->create('receipt.pdf', 100, 'application/pdf'),
        ])
        ->assertRedirect(route('customer.payment'));

    expect($booking->paymentReceipts()->count())->toBe(1);
});

it('shows receipt history instead of redirecting once a booking with uploaded receipts is fully settled', function () {
    Storage::fake('local');
    [$customer, $booking] = customerPaymentTestBooking(BookingStatus::Reserved);

    $path = UploadedFile::fake()->create('receipt.pdf', 100, 'application/pdf')->store('payment-receipts', 'local');
    $booking->paymentReceipts()->create(['path' => $path, 'original_name' => 'receipt.pdf']);

    PaymentProvider::query()->firstOrCreate(['driver' => 'manual'], ['display_name' => 'Manual', 'enabled' => true]);
    app(PaymentService::class)->record($booking, User::factory()->create(), [
        'type' => 'full',
        'amount_sen' => $booking->price_sen,
        'payment_date' => now()->toDateString(),
    ]);

    $this->actingAs($customer, 'customers')
        ->get(route('customer.payment'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('payableBookings', 0)
            ->has('receiptsByBooking', 1)
            ->where('receiptsByBooking.0.id', $booking->id)
            ->has('receiptsByBooking.0.receipts', 1)
        );
});

it('lets a customer pay two live bookings independently', function () {
    [$customer, $first] = customerPaymentTestBooking(BookingStatus::AwaitingPayment);
    [, $second] = customerPaymentTestBooking(BookingStatus::AwaitingPayment);
    $second->update(['customer_id' => $customer->id]);

    $this->actingAs($customer, 'customers')
        ->get(route('customer.payment'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->has('payableBookings', 2));

    $this->actingAs($customer, 'customers')
        ->post(route('customer.payment'), ['booking_id' => $second->id, 'payment_option' => 'full'])
        ->assertRedirect(route('customer.payment'));

    expect($first->refresh()->payment_option)->toBeNull();
    expect($second->refresh()->payment_option)->toBe('full');
});

it('groups receipts by booking so a customer with multiple bookings can tell them apart', function () {
    Storage::fake('local');
    [$customer, $booking] = customerPaymentTestBooking();

    $otherBooking = $customer->bookings()->create([
        'ref_no' => 'BK-OTHER', 'variant_id' => $booking->variant_id, 'address_id' => $booking->address_id,
        'edd' => now()->addDays(60), 'expected_birth_type' => 'normal', 'duration_days' => 7,
        'meal_plan' => 'lunch_dinner', 'slots_used' => 1, 'status' => BookingStatus::Cancelled,
        'price_sen' => 35000, 'daily_rate_sen' => 5000, 'policy_snapshot' => [],
        'projected_period' => '['.now()->addDays(60)->toDateString().','.now()->addDays(66)->toDateString().']',
    ]);

    $path = UploadedFile::fake()->create('receipt.pdf', 100, 'application/pdf')->store('payment-receipts', 'local');
    $booking->paymentReceipts()->create(['path' => $path, 'original_name' => 'current.pdf']);
    $otherBooking->paymentReceipts()->create(['path' => $path, 'original_name' => 'older.pdf']);

    $this->actingAs($customer, 'customers')
        ->get(route('customer.payment'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->has('receiptsByBooking', 2));
});
