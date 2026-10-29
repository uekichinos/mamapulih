<?php

namespace App\Domain\Payments;

use App\Domain\Notifications\CustomerMailer;
use App\Models\Booking;
use Barryvdh\DomPDF\Facade\Pdf;

class ReceiptMailer
{
    public function __construct(private CustomerMailer $mailer) {}

    public function send(Booking $booking): void
    {
        $booking->loadMissing(['customer', 'variant.package', 'payments']);

        $pdf = Pdf::loadView('pdf.receipt', ['booking' => $booking])->output();

        $this->mailer->send($booking, fn () => new ReceiptMail($booking, $pdf));
    }
}
