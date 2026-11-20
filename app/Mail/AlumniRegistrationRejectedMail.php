<?php

namespace App\Mail;

use App\Models\AlumniRegistration;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AlumniRegistrationRejectedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public AlumniRegistration $registration)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Pendaftaran Akun Alumni SIPALA Ditolak',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.alumni_rejected',
            with: [
                'registration' => $this->registration,
                'registerUrl' => route('alumni.register'),
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}

