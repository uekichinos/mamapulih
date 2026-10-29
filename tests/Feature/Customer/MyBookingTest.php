<?php

use App\Domain\Notifications\BookingCancelledByCustomerMail;
use App\Domain\Payments\PaymentService;
use App\Enums\BookingStatus;
use App\Models\Address;
use App\Models\Booking;
use App\Models\BusinessProfile;
use App\Models\Customer;
use App\Models\Package;
use App\Models\PaymentProvider;
use App\Models\User;
use App\Models\Zone;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

it('shows nothing yet for a customer with no booking', function () {
    $customer = Customer::factory()->create();

    $this->actingAs($customer, 'customers')
        ->get(route('customer.my-booking'))
        ->assertOk();
});

it('is not reachable without logging in as a customer', function () {
    $this->get(route('customer.my-booking'))->assertRedirect(route('customer.login'));
});

function myBookingTestBooking(BookingStatus $status = BookingStatus::PendingApproval)
{
    $package = Package::query()->create(['name' => ['en' => 'Standard', 'ms' => 'Standard']]);
    $variant = $package->variants()->create([
        'birth_type' => 'normal', 'duration_days' => 7, 'meal_plan' => 'lunch_dinner',
        'meals_per_day' => 2, 'price_sen' => 350000,
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
        'edd' => now()->addDays(30),
        'expected_birth_type' => 'normal',
        'duration_days' => 7,
        'meal_plan' => 'lunch_dinner',
        'slots_used' => 1,
        'status' => $status,
        'price_sen' => 350000,
        'daily_rate_sen' => 50000,
        'policy_snapshot' => [],
        'projected_period' => '['.now()->addDays(30)->toDateString().','.now()->addDays(36)->toDateString().']',
    ]);

    return [$customer, $booking];
}

it('lets a customer cancel a pending-approval booking with no payment', function () {
    Mail::fake();
    BusinessProfile::current()->update(['email' => 'owner@example.com']);
    [$customer, $booking] = myBookingTestBooking(BookingStatus::PendingApproval);

    $this->actingAs($customer, 'customers')
        ->get(route('customer.my-booking'))
        ->assertInertia(fn ($page) => $page->where('canCancel', true));

    $this->actingAs($customer, 'customers')
        ->post(route('customer.my-booking.cancel'), ['reason' => 'Changed my mind'])
        ->assertRedirect(route('customer.my-booking'));

    expect($booking->refresh()->status)->toBe(BookingStatus::Cancelled);
    Mail::assertSent(BookingCancelledByCustomerMail::class, 1);
});

it('lets a customer cancel an awaiting-payment booking with no payment', function () {
    [$customer, $booking] = myBookingTestBooking(BookingStatus::AwaitingPayment);

    $this->actingAs($customer, 'customers')
        ->post(route('customer.my-booking.cancel'))
        ->assertRedirect(route('customer.my-booking'));

    expect($booking->refresh()->status)->toBe(BookingStatus::Cancelled);
});

it('refuses to self-cancel once a payment has already been recorded, even below the reserve threshold', function () {
    [$customer, $booking] = myBookingTestBooking(BookingStatus::AwaitingPayment);

    PaymentProvider::query()->firstOrCreate(['driver' => 'manual'], ['display_name' => 'Manual', 'enabled' => true]);
    app(PaymentService::class)->record($booking, User::factory()->create(), [
        'type' => 'deposit',
        'amount_sen' => 1000,
        'payment_date' => now()->toDateString(),
    ]);

    $this->actingAs($customer, 'customers')
        ->get(route('customer.my-booking'))
        ->assertInertia(fn ($page) => $page->where('canCancel', true));

    $this->actingAs($customer, 'customers')
        ->post(route('customer.my-booking.cancel'))
        ->assertStatus(422);

    expect($booking->refresh()->status)->toBe(BookingStatus::AwaitingPayment);
});

it('refuses to self-cancel once the booking has moved past awaiting payment', function () {
    [$customer, $booking] = myBookingTestBooking(BookingStatus::Reserved);

    $this->actingAs($customer, 'customers')
        ->get(route('customer.my-booking'))
        ->assertInertia(fn ($page) => $page->where('canCancel', false));

    $this->actingAs($customer, 'customers')
        ->post(route('customer.my-booking.cancel'))
        ->assertStatus(422);

    expect($booking->refresh()->status)->toBe(BookingStatus::Reserved);
});

it('lists older bookings as archives, excluding the latest one', function () {
    [$customer] = myBookingTestBooking(BookingStatus::Cancelled);
    [, $latest] = myBookingTestBooking(BookingStatus::PendingApproval);
    $latest->update(['customer_id' => $customer->id]);

    $this->actingAs($customer, 'customers')
        ->get(route('customer.my-booking'))
        ->assertInertia(fn ($page) => $page
            ->where('booking.id', $latest->id)
            ->has('archivedBookings', 1)
        );
});

it('treats the latest booking as archived when it is inactive, falling back to an earlier live one', function (BookingStatus $inactive) {
    [$customer] = myBookingTestBooking(BookingStatus::AwaitingPayment);
    [, $latestInactive] = myBookingTestBooking($inactive);
    $latestInactive->update(['customer_id' => $customer->id]);

    $this->actingAs($customer, 'customers')
        ->get(route('customer.my-booking'))
        ->assertInertia(fn ($page) => $page
            ->where('booking.status', 'awaiting_payment')
            ->has('archivedBookings', 1)
        );
})->with([
    'rejected' => BookingStatus::Rejected,
    'expired' => BookingStatus::Expired,
    'cancelled' => BookingStatus::Cancelled,
    'completed' => BookingStatus::Completed,
]);

it('shows no current booking when every booking is inactive', function (BookingStatus $inactive) {
    [$customer] = myBookingTestBooking($inactive);

    $this->actingAs($customer, 'customers')
        ->get(route('customer.my-booking'))
        ->assertInertia(fn ($page) => $page
            ->where('booking', null)
            ->has('archivedBookings', 1)
        );
})->with([
    'rejected' => BookingStatus::Rejected,
    'expired' => BookingStatus::Expired,
    'cancelled' => BookingStatus::Cancelled,
    'completed' => BookingStatus::Completed,
]);

it('lets a customer view one of their own archived bookings', function () {
    [$customer, $booking] = myBookingTestBooking(BookingStatus::Cancelled);

    $this->actingAs($customer, 'customers')
        ->get(route('customer.my-booking.archive', $booking))
        ->assertInertia(fn ($page) => $page->where('booking.ref_no', $booking->ref_no));
});

it('refuses to let a customer view another customer\'s archived booking', function () {
    [, $booking] = myBookingTestBooking(BookingStatus::Cancelled);
    $other = Customer::factory()->create();

    $this->actingAs($other, 'customers')
        ->get(route('customer.my-booking.archive', $booking))
        ->assertStatus(403);
});

it('lets a customer view a receipt they uploaded against their current booking', function () {
    Storage::fake('local');
    [$customer, $booking] = myBookingTestBooking(BookingStatus::AwaitingPayment);

    $path = UploadedFile::fake()->create('receipt.pdf', 100, 'application/pdf')
        ->store('payment-receipts', 'local');
    $receipt = $booking->paymentReceipts()->create(['path' => $path, 'original_name' => 'receipt.pdf']);

    $this->actingAs($customer, 'customers')
        ->get(route('customer.my-booking.receipts.view', $receipt))
        ->assertOk();
});

it('refuses to let a customer view another customer\'s receipt', function () {
    Storage::fake('local');
    [, $booking] = myBookingTestBooking(BookingStatus::AwaitingPayment);
    $other = Customer::factory()->create();

    $path = UploadedFile::fake()->create('receipt.pdf', 100, 'application/pdf')
        ->store('payment-receipts', 'local');
    $receipt = $booking->paymentReceipts()->create(['path' => $path, 'original_name' => 'receipt.pdf']);

    $this->actingAs($other, 'customers')
        ->get(route('customer.my-booking.receipts.view', $receipt))
        ->assertStatus(403);
});
