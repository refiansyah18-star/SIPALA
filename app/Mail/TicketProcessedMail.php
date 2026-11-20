<?php

namespace App\Mail;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TicketProcessedMail extends Mailable
{
    use Queueable, SerializesModels;

    public Ticket $ticket;
    public string $note;

    /**
     * Create a new message instance.
     */
    public function __construct(Ticket $ticket, string $note)
    {
        $this->ticket = $ticket;
        $this->note = $note;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Tiket: {$this->ticket->ticket_number} sedang diproses",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.ticket_processed',
            with: [
                'ticket' => $this->ticket,
                'user' => $this->ticket->user,
                'service' => $this->ticket->service,
                'note' => $this->note,
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

