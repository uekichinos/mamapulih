<?php

namespace App\Domain\Notifications;

use App\Domain\Settings\SmtpSettings;
use App\Models\Booking;
use Closure;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Mail;

/**
 * The single choke point every customer-facing automatic message sends
 * through: enforces the message-safety rule, skips a blank email, and
 * sets the mail locale from the customer's own preference — needed
 * because SetLocale is a web-request middleware and never runs inside
 * the scheduled commands most of these triggers fire from.
 */
class CustomerMailer
{
    public function __construct(private SmtpSettings $smtp) {}

    public function send(Booking $booking, Closure $mailableFactory): void
    {
        if (! $booking->canReceiveAutomaticMessages()) {
            return;
        }

        $this->sendRegardless($booking, $mailableFactory);
    }

    /**
     * For the handful of operational notices (pause/resume) that must
     * still reach the customer even while their booking is marked
     * medical — the message-safety rule is about suppressing *reminders*
     * during a medical pause, not about hiding the pause itself. Still
     * skips a customer who is genuinely do-not-contact or compassionate.
     */
    public function sendRegardless(Booking $booking, Closure $mailableFactory): void
    {
        $email = $booking->customer->email;

        if (blank($email)) {
            return;
        }

        $previousLocale = App::getLocale();
        App::setLocale($booking->customer->preferred_locale ?: $previousLocale);

        try {
            $this->smtp->applyToRuntimeConfig();

            /** @var Mailable $mailable */
            $mailable = $mailableFactory();

            Mail::to($email)->send($mailable);
        } finally {
            App::setLocale($previousLocale);
        }
    }
}
