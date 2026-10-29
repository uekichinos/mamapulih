<?php

namespace App\Console\Commands;

use App\Domain\Audit\AuditLogger;
use App\Domain\Booking\ConsentService;
use App\Domain\Notifications\CustomerMailer;
use App\Domain\Notifications\DayBeforeStartMail;
use App\Domain\Notifications\PackageCompleteMail;
use App\Domain\Notifications\PaymentDeadlineNearMail;
use App\Domain\Notifications\TwoDaysLeftMail;
use App\Domain\Notifications\WeekCheckInMail;
use App\Enums\BookingStatus;
use App\Models\AuditLog;
use App\Models\Booking;
use App\Models\PlanChange;
use Illuminate\Console\Command;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Collection;

class SendRemindersCommand extends Command
{
    protected $signature = 'mamapulih:send-reminders';

    protected $description = 'Send scheduled customer reminders: payment deadline, week-37 check-in, consent expiring, day-before-start, two-days-left, and package complete';

    public function __construct(
        private CustomerMailer $mailer,
        private ConsentService $consents,
        private AuditLogger $audit,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $paymentReminders = $this->remindPaymentDeadline();
        $weekCheckIns = $this->remindWeekCheckIn();
        $consentReminders = $this->remindConsentExpiring();
        $dayBeforeStarts = $this->remindDayBeforeStart();
        $twoDaysLeft = $this->remindTwoDaysLeft();
        $completed = $this->completePackages();

        $this->components->info(sprintf(
            '%d payment reminder(s), %d week-check-in(s), %d consent reminder(s), %d day-before-start(s), %d two-days-left(s), %d package(s) completed.',
            $paymentReminders,
            $weekCheckIns,
            $consentReminders,
            $dayBeforeStarts,
            $twoDaysLeft,
            $completed,
        ));

        return self::SUCCESS;
    }

    private function remindPaymentDeadline(): int
    {
        $bookings = Booking::query()
            ->where('status', BookingStatus::AwaitingPayment)
            ->whereNotNull('payment_due_at')
            ->whereBetween('payment_due_at', [now(), now()->addHours(24)])
            ->get();

        return $this->notifyOnce($bookings, 'notification.payment_deadline_near', fn (Booking $b) => new PaymentDeadlineNearMail($b));
    }

    private function remindWeekCheckIn(): int
    {
        $bookings = Booking::query()
            ->where('status', BookingStatus::Reserved)
            ->whereBetween('edd', [today()->addDays(14)->toDateString(), today()->addDays(21)->toDateString()])
            ->get();

        return $this->notifyOnce($bookings, 'notification.week_check_in', fn (Booking $b) => new WeekCheckInMail($b));
    }

    private function remindConsentExpiring(): int
    {
        $planChanges = PlanChange::query()
            ->where('status', 'awaiting_consent')
            ->whereHas('consent', fn ($q) => $q->whereNull('decision')->whereBetween('expires_at', [now(), now()->addHours(12)]))
            ->get();

        foreach ($planChanges as $planChange) {
            $this->consents->resend($planChange);
        }

        return $planChanges->count();
    }

    private function remindDayBeforeStart(): int
    {
        $bookings = Booking::query()
            ->where('status', BookingStatus::Active)
            ->whereDate('start_date', today()->addDay())
            ->get();

        return $this->notifyOnce($bookings, 'notification.day_before_start', fn (Booking $b) => new DayBeforeStartMail($b));
    }

    private function remindTwoDaysLeft(): int
    {
        $bookings = Booking::query()
            ->where('status', BookingStatus::Active)
            ->whereDate('end_date', today()->addDays(2))
            ->get();

        return $this->notifyOnce($bookings, 'notification.two_days_left', fn (Booking $b) => new TwoDaysLeftMail($b));
    }

    private function completePackages(): int
    {
        $bookings = Booking::query()
            ->where('status', BookingStatus::Active)
            ->whereDate('end_date', '<', today())
            ->get();

        foreach ($bookings as $booking) {
            $before = $booking->toArray();
            $booking->update(['status' => BookingStatus::Completed]);
            $this->audit->record('booking.completed', $booking, $before, $booking->toArray());

            $this->mailer->send($booking, fn () => new PackageCompleteMail($booking));
        }

        return $bookings->count();
    }

    /**
     * @param  Collection<int, Booking>  $bookings
     * @param  \Closure(Booking): Mailable  $mailableFactory
     */
    private function notifyOnce($bookings, string $action, \Closure $mailableFactory): int
    {
        $sent = 0;

        foreach ($bookings as $booking) {
            $alreadySent = AuditLog::query()
                ->where('action', $action)
                ->where('subject_type', $booking->getMorphClass())
                ->where('subject_id', $booking->id)
                ->exists();

            if ($alreadySent) {
                continue;
            }

            $this->mailer->send($booking, fn () => $mailableFactory($booking));
            $this->audit->record($action, $booking);
            $sent++;
        }

        return $sent;
    }
}
