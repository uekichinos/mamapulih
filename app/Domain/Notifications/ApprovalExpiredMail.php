<?php

namespace App\Domain\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ApprovalExpiredMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Booking $booking) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: "Your slot was released — {$this->booking->ref_no}");
    }

    public function content(): Content
    {
        return new Content(view: 'emails.approval-expired', with: ['booking' => $this->booking]);
    }
}
