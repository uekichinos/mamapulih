<?php

namespace App\Domain\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PausedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Booking $booking) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: "Deliveries paused — {$this->booking->ref_no}");
    }

    public function content(): Content
    {
        return new Content(view: 'emails.paused', with: ['booking' => $this->booking]);
    }
}
