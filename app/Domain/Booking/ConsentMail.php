<?php

namespace App\Domain\Booking;

use App\Models\PlanChange;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ConsentMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public PlanChange $planChange, public string $token) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: "Please confirm your plan — {$this->planChange->booking->ref_no}");
    }

    public function content(): Content
    {
        return new Content(view: 'emails.consent', with: [
            'planChange' => $this->planChange,
            'url' => route('customer.consent.show', ['token' => $this->token]),
        ]);
    }
}
