<?php

namespace App\Listeners;

use App\Events\OrderBookingFailedEvent;
use App\Events\OrderBookingSuccessEvent;
use App\Events\OrderCreatedEvent;
use App\Events\OrderSuccessPaidEvent;
use App\Jobs\SendNotificationOnSuccessPaymentJob;
use App\Services\MailService;
use App\Services\PaymentService;

readonly class OrderBookingFailedListener
{
    public function __construct(
        private MailService $mailService
    ) {}

    public function handle(OrderBookingFailedEvent $event): void
    {
        // отправляем сообщение об ошибке бронирования
        $this->mailService::sendBookingMessage(
            to: config("services.booking.notificationEmail"),
            subject: "❌ Ошибка бронирования оплаченного заказа",
            messageText: "Заказ не забронирован. Ответ Booking: " . $event->reason,
            orderDTO: $event->orderDTO
        );
    }
}
