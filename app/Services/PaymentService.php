<?php

namespace App\Services;

use App\DTO\BookingRequestDTO;
use App\DTO\OrderDTO;
use App\Events\PaymentLinkCreatedEvent;
use App\Models\Order;

class PaymentService
{
    public function createPayment(OrderDTO $order): array {
        $paymentLink = "https://ya.ru";

        $this->savePaymentLink(order: $order, paymentLink: $paymentLink);

        $order->paymentLink = $paymentLink;

        event(new PaymentLinkCreatedEvent(orderDTO: $order));

        return [
            'paymentLink' => $paymentLink,
            'expiresAt' => $order->expiresAt->toIso8601String(),
        ];
    }

    private function savePaymentLink(OrderDTO $order, ?string $paymentLink): void {
        Order::query()->where("order_id", $order->orderId)->update(['payment_link' => $paymentLink]);
    }
}
