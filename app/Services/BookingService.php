<?php

namespace App\Services;

use App\DTO\BookingRequestDTO;
use App\Models\Order;

readonly class BookingService
{
    public function __construct(
        private PaymentService $paymentService,
    ) {}

    /*
     * Метод сохраняет заказ в базе и возвращает ссылку на оплату
     */
    public function processOrder(array $data): string {
        $bookingRequestDTO = BookingRequestDTO::fromArray(data: $data);

        $this->saveOrder(bookingRequestDTO: $bookingRequestDTO);

        return $this->paymentService->createPayment(bookingRequestDTO: $bookingRequestDTO);
    }

    public function saveOrder(BookingRequestDTO $bookingRequestDTO): void {

    }
}
