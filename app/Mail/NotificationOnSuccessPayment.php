<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NotificationOnSuccessPayment extends Mailable
{
    use Queueable, SerializesModels;

    public string $messageSubject;
    public string $orderId;
    public string $orderCode;

    /**
     * Create a new message instance.
     */
    public function __construct(string $messageSubject, string $orderId, string $orderCode)
    {
        $this->messageSubject = $messageSubject;
        $this->orderId = $orderId;
        $this->orderCode = $orderCode;
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
            markdown: 'emails.success_payment',
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
