<?php

use App\Domain\Booking\BookingService;
use App\Domain\Notifications\BookingApprovedMail;
use App\Domain\Notifications\CustomerMailer;
use App\Enums\BookingSensitiveFlag;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

it('never sends an automatic message for a compassionate booking', function () {
    Mail::fake();

    $booking = kitchenTestBooking('lunch', 7);
    $booking->update(['sensitive_flag' => BookingSensitiveFlag::Compassionate]);

    app(CustomerMailer::class)->send($booking, fn () => new BookingApprovedMail($booking));

    Mail::assertNothingSent();
});

it('never sends an automatic message for a medical-paused booking', function () {
    Mail::fake();

    $booking = kitchenTestBooking('lunch', 7);
    $booking->update(['sensitive_flag' => BookingSensitiveFlag::Medical]);

    app(CustomerMailer::class)->send($booking, fn () => new BookingApprovedMail($booking));

    Mail::assertNothingSent();
});

it('never sends an automatic message for a do-not-contact booking, even indirectly via the customer flag', function () {
    Mail::fake();

    $booking = kitchenTestBooking('lunch', 7);
    $booking->customer->update(['do_not_contact' => true]);

    app(CustomerMailer::class)->send($booking->refresh(), fn () => new BookingApprovedMail($booking));

    Mail::assertNothingSent();
});

it('sends normally once a booking has no sensitive flag and the customer is contactable', function () {
    Mail::fake();

    $booking = kitchenTestBooking('lunch', 7);

    app(CustomerMailer::class)->send($booking, fn () => new BookingApprovedMail($booking));

    Mail::assertSent(BookingApprovedMail::class, 1);
});

it('stays silent across the booking approve/reject triggers for a compassionate booking', function () {
    Mail::fake();

    $booking = kitchenTestBooking('lunch', 7);
    $booking->update(['sensitive_flag' => BookingSensitiveFlag::Compassionate]);

    app(BookingService::class)->approve($booking, User::factory()->create());

    Mail::assertNothingSent();
});
