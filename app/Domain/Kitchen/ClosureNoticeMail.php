<?php

namespace App\Domain\Kitchen;

use App\Models\Booking;
use App\Models\ClosureDay;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ClosureNoticeMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Booking $booking, public ClosureDay $closureDay) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: "Kitchen closure affecting your booking — {$this->booking->ref_no}");
    }

    public function content(): Content
    {
        return new Content(view: 'emails.closure-notice', with: [
            'booking' => $this->booking,
            'closureDay' => $this->closureDay,
        ]);
    }
}
