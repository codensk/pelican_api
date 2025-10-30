<?php

namespace App\Listeners;

use App\Events\OrderCreatedEvent;
use App\Events\PaymentLinkCreatedEvent;
use App\Jobs\SendPaymentLinkOnOrderCreatedJob;

class PaymentLinkCreatedListener
{
    public function __construct() {}

    public function handle(PaymentLinkCreatedEvent $event): void
    {
        // Отправляем ссылку на оплату
        if ($event->orderDTO->notificationEmail) {
            dispatch(job: new SendPaymentLinkOnOrderCreatedJob(email: $event->orderDTO->notificationEmail, orderDTO: $event->orderDTO));
        }
    }
}
