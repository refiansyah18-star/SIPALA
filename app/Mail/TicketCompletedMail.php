<?php

namespace App\Mail;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TicketCompletedMail extends Mailable
{
    use Queueable, SerializesModels;

    public Ticket $ticket;
    public string $resolutionNote;

    /**
     * Create a new message instance.
     */
    public function __construct(Ticket $ticket, string $resolutionNote)
    {
        $this->ticket = $ticket;
        $this->resolutionNote = $resolutionNote;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Tiket: {$this->ticket->ticket_number} selesai",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.ticket_completed',
            with: [
                'ticket' => $this->ticket,
                'user' => $this->ticket->user,
                'service' => $this->ticket->service,
                'resolutionNote' => $this->resolutionNote,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     */
    public function attachments(): array
    {
        return [];
    }
}
