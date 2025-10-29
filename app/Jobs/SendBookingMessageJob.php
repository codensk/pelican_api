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
    public string $messageText;
    public array $orderDetails;
    public MailService $mailService;

    public function __construct(string $email, string $messageText, array $orderDetails)
    {
        $this->email = $email;
        $this->messageText = $messageText;
        $this->orderDetails = $orderDetails;
        $this->mailService = app(MailService::class);
    }

    public function handle(): void
    {
        if (! defined('CURL_SSLVERSION_TLSv1_2')) {
            define('CURL_SSLVERSION_TLSv1_2', 6); // Значение 6 соответствует TLS 1.2
        }

        try {
            $this->mailService::sendNotificationOnSuccessPayment(
                to: $this->email,
                subject: "💸 Заказ успешно оплачен",
                orderId: $this->orderDetails['orderId'],
            );
        } catch (Exception $exception) {
            $this->release(10);
        }
    }
}
