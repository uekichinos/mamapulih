<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SmtpTestMail extends Mailable
{
    use Queueable, SerializesModels;

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'MamaPulih test email');
    }

    public function content(): Content
    {
        return new Content(
            htmlString: '<p>This is a test email from MamaPulih. If you received this, your SMTP settings are working.</p>',
        );
    }
}
