<?php

namespace App\Mail;

use App\Models\AlumniRegistration;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AlumniRegistrationApprovedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public AlumniRegistration $registration,
        public User $user
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Akun Alumni SIPALA Anda Telah Disetujui & Aktif',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.alumni_approved',
            with: [
                'registration' => $this->registration,
                'user' => $this->user,
                'loginUrl' => route('login'),
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}

