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

class SendNotificationOnSuccessPaymentJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public string $email;
    public OrderDTO $orderDTO;
    public MailService $mailService;

    public function __construct(string $email, OrderDTO $orderDTO)
    {
        $this->email = $email;
        $this->orderDTO = $orderDTO;
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
                orderId: $this->orderDTO->orderId,
                orderCode: $this->orderDTO->code
            );
        } catch (Exception $exception) {
            $this->release(10);
        }
    }
}
