<?php

use App\Domain\Notifications\DayBeforeStartMail;
use App\Domain\Notifications\PackageCompleteMail;
use App\Domain\Notifications\PaymentDeadlineNearMail;
use App\Domain\Notifications\TwoDaysLeftMail;
use App\Domain\Notifications\WeekCheckInMail;
use App\Enums\BookingStatus;
use App\Models\AuditLog;
use Illuminate\Support\Facades\Mail;

it('sends a payment-deadline-near reminder once and does not duplicate on a second run', function () {
    Mail::fake();

    $booking = expiryTestBooking([
        'status' => BookingStatus::AwaitingPayment,
        'payment_due_at' => now()->addHours(12),
    ]);

    $this->artisan('mamapulih:send-reminders')->assertSuccessful();
    Mail::assertSent(PaymentDeadlineNearMail::class, 1);

    $this->artisan('mamapulih:send-reminders');
    Mail::assertSent(PaymentDeadlineNearMail::class, 1);
});

it('sends a week-37 check-in for a reserved booking whose edd is 14-21 days out', function () {
    Mail::fake();

    expiryTestBooking(['status' => BookingStatus::Reserved, 'edd' => now()->addDays(18)]);

    $this->artisan('mamapulih:send-reminders');

    Mail::assertSent(WeekCheckInMail::class, 1);
});

it('sends a day-before-start reminder for an active booking starting tomorrow', function () {
    Mail::fake();

    expiryTestBooking([
        'status' => BookingStatus::Active,
        'start_date' => now()->addDay(),
        'end_date' => now()->addDays(7),
    ]);

    $this->artisan('mamapulih:send-reminders');

    Mail::assertSent(DayBeforeStartMail::class, 1);
});

it('sends a two-days-left reminder for an active booking ending in two days', function () {
    Mail::fake();

    expiryTestBooking([
        'status' => BookingStatus::Active,
        'start_date' => now()->subDays(5),
        'end_date' => now()->addDays(2),
    ]);

    $this->artisan('mamapulih:send-reminders');

    Mail::assertSent(TwoDaysLeftMail::class, 1);
});

it('completes a package once its end date has passed and sends the thank-you mail', function () {
    Mail::fake();

    $booking = expiryTestBooking([
        'status' => BookingStatus::Active,
        'start_date' => now()->subDays(10),
        'end_date' => now()->subDay(),
    ]);

    $this->artisan('mamapulih:send-reminders');

    expect($booking->refresh()->status)->toBe(BookingStatus::Completed);
    Mail::assertSent(PackageCompleteMail::class, 1);
});

it('records an audit entry for a dispatched reminder so a second run does not resend it', function () {
    Mail::fake();

    $booking = expiryTestBooking([
        'status' => BookingStatus::AwaitingPayment,
        'payment_due_at' => now()->addHours(6),
    ]);

    $this->artisan('mamapulih:send-reminders');

    expect(AuditLog::query()
        ->where('action', 'notification.payment_deadline_near')
        ->where('subject_type', $booking->getMorphClass())
        ->where('subject_id', $booking->id)
        ->exists())->toBeTrue();
});
