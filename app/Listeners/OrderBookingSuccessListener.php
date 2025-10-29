<?php

namespace App\Listeners;

use App\Events\OrderBookingSuccessEvent;
use App\Events\OrderCreatedEvent;
use App\Events\OrderSuccessPaidEvent;
use App\Jobs\SendNotificationOnSuccessPaymentJob;
use App\Services\MailService;
use App\Services\PaymentService;

readonly class OrderBookingSuccessListener
{
    public function __construct(
        private MailService $mailService
    ) {}

    public function handle(OrderBookingSuccessEvent $event): void
    {
        // отправляем сообщение об успешном бронировании
        $this->mailService::sendBookingMessage(
            to: config("services.booking.notificationEmail"),
            subject: "✅ Забронирован новый заказ",
            messageText: "Успешно забронирован новый заказ в системе Pelican",
            orderDTO: $event->orderDTO
        );
    }
}
