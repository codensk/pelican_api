<?php

namespace App\Jobs;

use App\DTO\OrderDTO;
use App\DTO\UserDTO;
use App\Services\MailService;
use App\Services\UserService;
use Exception;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendBookingMessageJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public string $email;
    public string $subject;
    public string $messageText;
    public OrderDTO $orderDTO;
    public MailService $mailService;

    public function __construct(string $email, string $subject, string $messageText, OrderDTO $orderDTO)
    {
        $this->email = $email;
        $this->subject = $subject;
        $this->messageText = $messageText;
        $this->orderDTO = $orderDTO;
        $this->mailService = app(MailService::class);
    }

    public function handle(): void
    {
        if (! defined('CURL_SSLVERSION_TLSv1_2')) {
            define('CURL_SSLVERSION_TLSv1_2', 6); // Значение 6 соответствует TLS 1.2
        }

        try {
            $this->mailService::sendBookingMessage(
                to: $this->email,
                subject: $this->subject,
                messageText: $this->messageText,
                orderDTO: $this->orderDTO
            );
        } catch (Exception $exception) {
            $this->release(10);
        }
    }
}
