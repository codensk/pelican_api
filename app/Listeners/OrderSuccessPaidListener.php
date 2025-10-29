<?php

namespace App\Listeners;

use App\Events\OrderCreatedEvent;
use App\Events\OrderSuccessPaidEvent;
use App\Jobs\SendNotificationOnSuccessPaymentJob;
use App\Services\PaymentService;

class OrderSuccessPaidListener
{
    public function __construct(
        private readonly PaymentService $paymentService
    ) {}

    public function handle(OrderSuccessPaidEvent $event): void
    {
        // Отправляем подтверждение об успешной оплате
        $this->paymentService->sendNotificationOnSuccessPayment(orderId: $event->orderId);
    }
}
