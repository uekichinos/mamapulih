<?php

namespace App\Domain\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Internal notice to the business itself — not a customer-facing
 * automatic message, so it does not go through CustomerMailer/the
 * message-safety rule.
 */
class NewBookingToReviewMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Booking $booking) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: "Booking awaiting approval — {$this->booking->ref_no}");
    }

    public function content(): Content
    {
        return new Content(view: 'emails.new-booking-to-review', with: ['booking' => $this->booking]);
    }
}
