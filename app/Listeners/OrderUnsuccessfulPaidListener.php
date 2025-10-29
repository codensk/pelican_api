<?php

namespace App\Listeners;

use App\Events\OrderCreatedEvent;
use App\Events\OrderSuccessPaidEvent;
use App\Events\OrderUnsuccessfulPaidEvent;
use App\Jobs\SendNotificationOnSuccessPaymentJob;
use App\Models\Order;
use App\Services\MailService;
use App\Services\PaymentService;

readonly class OrderUnsuccessfulPaidListener
{
    public function __construct(
        private MailService    $mailService,
        private PaymentService $paymentService,
    ) {}

    public function handle(OrderUnsuccessfulPaidEvent $event): void
    {
        // Отправляем пменеджеру информацию об ошибке оплаты
        $order = Order::query()->where("order_id", $event->orderId)->first();
        $this->mailService::sendBookingMessage(
            to: config("services.booking.notificationEmail"),
            subject: "❌ Ошибка оплаты",
            messageText: "Во время оплаты произошла ошибка оплаты. Заказ не оформлен",
            orderDTO: $order->toDto()
        );
    }
}
