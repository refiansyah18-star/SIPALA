<?php

namespace App\Mail;

use App\Models\AlumniRegistration;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AlumniRegistrationSubmittedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public AlumniRegistration $registration)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Pendaftaran Akun Alumni SIPALA Diterima — Menunggu Verifikasi',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.alumni_submitted',
            with: [
                'registration' => $this->registration,
                'statusUrl' => route('alumni.status'),
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}

