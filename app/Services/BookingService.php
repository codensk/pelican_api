<?php

namespace App\Services;

use App\DTO\BookingRequestDTO;
use App\DTO\OrderPriceDTO;
use App\Models\Order;
use App\Models\PriceHistory;

readonly class BookingService
{
    public function __construct(
        private PaymentService $paymentService,
    ) {}

    /**
     * Метод сохраняет заказ в базе и возвращает ссылку на оплату
     */
    public function processOrder(array $data): string {
        $bookingRequestDTO = BookingRequestDTO::fromArray(data: $data);

        $this->saveOrder(bookingRequestDTO: $bookingRequestDTO);

        return $this->paymentService->createPayment(bookingRequestDTO: $bookingRequestDTO);
    }


    /**
     * Сохраняет заказ в базе до оплаты
     *
     * @param BookingRequestDTO $bookingRequestDTO
     * @param int|null $userId
     * @return Order
     */
    public function saveOrder(BookingRequestDTO $bookingRequestDTO, ?int $userId = null): Order {
        return Order::query()->create([
            'user_id' => $userId,
            'price_id' => $bookingRequestDTO->priceId,
            'full_price' => $bookingRequestDTO->priceId,
        ]);
    }

    public function calcPrice(BookingRequestDTO $bookingRequestDTO): OrderPriceDTO {
        $priceRow = PriceHistory::query()->where('price_id', $bookingRequestDTO->priceId)->first();

        if (!$priceRow) {
            abort(code: 404, message: 'Прайс не найден');
        }

        $price = $priceRow->price;

        // todo: подсчет стоимости услуг + справочник в букинге

        return OrderPriceDTO::fromArray(data: [
           'fullPrice' => $price->price,
           'tripPrice' => $price->price,
           'servicePrice' => $bookingRequestDTO,
        ]);
    }
}
