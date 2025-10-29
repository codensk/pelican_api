<?php

namespace App\Mail;

use App\DTO\OrderDTO;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BookingMessage extends Mailable
{
    use Queueable, SerializesModels;

    public string $messageSubject;
    public string $messageText;
    public array $orderData;
    public array $orderDetails;

    /**
     * Create a new message instance.
     */
    public function __construct(string $messageSubject, string $messageText, OrderDTO $orderDTO)
    {
        $this->messageSubject = $messageSubject;
        $this->messageText = $messageText;
        $this->orderData = $orderDTO->toArray();
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
            markdown: 'emails.booking_order_message',
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
