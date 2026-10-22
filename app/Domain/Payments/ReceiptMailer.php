<?php

namespace App\Domain\Payments;

use App\Domain\Settings\SmtpSettings;
use App\Models\Booking;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Mail;

class ReceiptMailer
{
    public function __construct(private SmtpSettings $smtp) {}

    public function send(Booking $booking): void
    {
        $email = $booking->customer->email;

        if (blank($email)) {
            return;
        }

        $booking->loadMissing(['customer', 'variant.package', 'payments']);

        $pdf = Pdf::loadView('pdf.receipt', ['booking' => $booking])->output();

        $this->smtp->applyToRuntimeConfig();

        Mail::to($email)->send(new ReceiptMail($booking, $pdf));
    }
}
