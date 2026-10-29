<?php

use App\Domain\Booking\CancellationService;
use App\Domain\Kitchen\BookingDayGenerator;
use App\Domain\Payments\PaymentService;
use App\Enums\BookingSensitiveFlag;
use App\Enums\BookingStatus;
use App\Models\PaymentProvider;
use App\Models\User;
use Symfony\Component\HttpKernel\Exception\HttpException;

function cancellationPayFor($booking, int $amountSen): void
{
    PaymentProvider::query()->firstOrCreate(['driver' => 'manual'], ['display_name' => 'Manual', 'enabled' => true]);

    app(PaymentService::class)->record($booking, User::factory()->create(), [
        'type' => 'full',
        'amount_sen' => $amountSen,
        'payment_date' => now()->toDateString(),
    ]);
}

it('refunds the full amount paid when cancelling before start', function () {
    $booking = kitchenTestBooking('lunch', 7);
    $booking->update(['status' => BookingStatus::AwaitingPayment, 'start_date' => null]);
    cancellationPayFor($booking, $booking->price_sen);

    $admin = User::factory()->create();
    $refund = app(CancellationService::class)->cancel($booking->refresh(), $admin, 'normal');

    expect($refund)->not->toBeNull();
    expect($refund->amount_sen)->toBe($booking->price_sen);
    expect($refund->status)->toBe('pending');
    expect($booking->refresh()->status)->toBe(BookingStatus::Cancelled);
});

it('refunds only the remaining scheduled days when cancelling after start', function () {
    $booking = kitchenTestBooking('lunch', 7);
    $booking->update(['start_date' => now()->subDays(4), 'end_date' => now()->addDays(2)]);
    app(BookingDayGenerator::class)->generateFor($booking->refresh());
    cancellationPayFor($booking, $booking->price_sen);

    $remainingDays = $booking->bookingDays()->where('status', 'scheduled')->where('date', '>=', now()->toDateString())->count();
    expect($remainingDays)->toBeLessThan(7);

    $admin = User::factory()->create();
    $refund = app(CancellationService::class)->cancel($booking->refresh(), $admin, 'normal');

    expect($refund->amount_sen)->toBe($remainingDays * $booking->daily_rate_sen);
});

it('always refunds the full amount for a compassionate cancellation, even after start', function () {
    $booking = kitchenTestBooking('lunch', 7);
    app(BookingDayGenerator::class)->generateFor($booking);
    cancellationPayFor($booking, $booking->price_sen);

    $admin = User::factory()->create();
    $refund = app(CancellationService::class)->cancel($booking->refresh(), $admin, 'compassionate', 'family request');

    expect($refund->amount_sen)->toBe($booking->price_sen);
    expect($booking->refresh()->sensitive_flag)->toBe(BookingSensitiveFlag::Compassionate);
});

it('marks the customer do-not-contact and silences the booking for a do_not_contact cancellation', function () {
    $booking = kitchenTestBooking('lunch', 7);

    $admin = User::factory()->create();
    app(CancellationService::class)->cancel($booking->refresh(), $admin, 'do_not_contact');

    $booking->refresh();
    expect($booking->sensitive_flag)->toBe(BookingSensitiveFlag::DoNotContact);
    expect($booking->customer->refresh()->do_not_contact)->toBeTrue();
    expect($booking->canReceiveAutomaticMessages())->toBeFalse();
});

it('deletes future scheduled booking days on cancellation', function () {
    $booking = kitchenTestBooking('lunch', 7);
    app(BookingDayGenerator::class)->generateFor($booking);
    $before = $booking->bookingDays()->count();

    app(CancellationService::class)->cancel($booking->refresh(), User::factory()->create(), 'normal');

    expect($before)->toBeGreaterThan(0);
    expect($booking->bookingDays()->where('status', 'scheduled')->count())->toBe(0);
});

it('rejects cancelling an already-cancelled booking', function () {
    $booking = kitchenTestBooking('lunch', 7);
    $booking->update(['status' => BookingStatus::Cancelled]);

    app(CancellationService::class)->cancel($booking, User::factory()->create(), 'normal');
})->throws(HttpException::class);
