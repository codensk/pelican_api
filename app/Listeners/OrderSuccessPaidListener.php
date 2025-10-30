<?php

namespace App\Listeners;

use App\Events\OrderCreatedEvent;
use App\Events\OrderSuccessPaidEvent;
use App\Jobs\SendBookingMessageJob;
use App\Jobs\SendNotificationOnSuccessPaymentJob;
use App\Models\Order;
use App\Services\MailService;
use App\Services\PaymentService;

readonly class OrderSuccessPaidListener
{
    public function __construct(
        private PaymentService $paymentService,
    ) {}

    public function handle(OrderSuccessPaidEvent $event): void
    {
        // Отправляем подтверждение успешной оплаты клиенту
        $this->paymentService->sendNotificationOnSuccessPayment(orderId: $event->orderId);

        // Отправляем подтверждение успешной оплаты менеджеру
        $order = Order::query()->where("order_id", $event->orderId)->first();
        dispatch(new SendBookingMessageJob(
            email: config("services.booking.notificationEmail"),
            subject: "💸 Оплачен новый заказ",
            messageText: "Пользователь успешно оплатил новый заказ",
            orderDTO: $order->toDto()
        ));

        // Помечаем заказ оплаченным
        $order->markAsPaid();
    }
}
