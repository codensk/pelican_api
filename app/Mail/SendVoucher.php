<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Storage;

class SendVoucher extends Mailable
{
    use Queueable, SerializesModels;

    public string $messageSubject;
    public string $orderId;
    public string $voucher;
    public string $orderCode;

    /**
     * Create a new message instance.
     */
    public function __construct(string $messageSubject, string $orderId, string $voucher, string $orderCode)
    {
        $this->messageSubject = $messageSubject;
        $this->orderId = $orderId;
        $this->voucher = $voucher;
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
            markdown: 'emails.voucher',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [
            Attachment::fromPath(Storage::path($this->voucher))
                ->as(basename($this->voucher))
        ];
    }
}
