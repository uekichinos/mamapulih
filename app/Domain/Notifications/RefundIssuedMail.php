<?php

namespace App\Domain\Notifications;

use App\Models\Refund;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RefundIssuedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Refund $refund) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: "Refund issued — {$this->refund->booking->ref_no}");
    }

    public function content(): Content
    {
        return new Content(view: 'emails.refund-issued', with: ['refund' => $this->refund]);
    }
}
