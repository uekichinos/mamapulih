<?php

namespace App\Domain\Payments;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ReceiptMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Booking $booking, public string $pdf) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: "Payment received — {$this->booking->ref_no}");
    }

    public function content(): Content
    {
        return new Content(view: 'emails.receipt', with: ['booking' => $this->booking]);
    }

    /**
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        return [
            Attachment::fromData(fn () => $this->pdf, "receipt-{$this->booking->ref_no}.pdf")
                ->withMime('application/pdf'),
        ];
    }
}
