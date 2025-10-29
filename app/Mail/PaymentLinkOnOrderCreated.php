<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PaymentLinkOnOrderCreated extends Mailable
{
    use Queueable, SerializesModels;

    public string $messageSubject;
    public string $orderId;
    public string $link;
    public string $expiresAt;

    /**
     * Create a new message instance.
     */
    public function __construct(string $messageSubject, string $orderId, string $link, string $expiresAt)
    {
        $this->messageSubject = $messageSubject;
        $this->orderId = $orderId;
        $this->link = $link;
        $this->expiresAt = $expiresAt;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->messageSubject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.payment_link_on_order_created',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
