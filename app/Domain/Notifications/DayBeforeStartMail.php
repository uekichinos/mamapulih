<?php

namespace App\Domain\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DayBeforeStartMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Booking $booking) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Your first delivery is tomorrow');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.day-before-start', with: ['booking' => $this->booking]);
    }
}
