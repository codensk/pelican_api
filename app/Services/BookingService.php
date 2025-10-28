<?php

namespace App\Services;

use App\DTO\BookingRequestDTO;
use App\DTO\OrderDTO;
use App\DTO\OrderPriceDTO;
use App\Events\OrderCreatedEvent;
use App\Models\Order;
use App\Models\PriceHistory;
use App\Models\Service;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

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

        $this->saveOrder(bookingRequestDTO: $bookingRequestDTO, userId: Auth::guard('api')->user()->id ?? null);

        return $this->paymentService->createPayment(bookingRequestDTO: $bookingRequestDTO);
    }


    /**
     * Сохраняет заказ в базе до оплаты
     *
     * @param BookingRequestDTO $bookingRequestDTO
     * @param int|null $userId
     * @return OrderDTO
     */
    public function saveOrder(BookingRequestDTO $bookingRequestDTO, ?int $userId = null): OrderDTO {
        $orderPrice = $this->calcPrice(bookingRequestDTO: $bookingRequestDTO);

        $orderId = Str::random(length: 10);

        $order = Order::query()->create([
            'user_id' => $userId,
            'price_id' => $bookingRequestDTO->priceId,
            'full_price' => $orderPrice->fullPrice,
            'order_price' => $orderPrice->tripPrice,
            'services_price' => $orderPrice->servicePrice,
            'order_id' => "PN-{$orderId}",
            'payload' => $bookingRequestDTO->toArray(),
            'is_paid' => false,
        ]);

        $orderDto = OrderDTO::fromArray(data: [
            'userId' => $order->user_id,
            'priceId' => $order->price_id,
            'orderId' => $order->order_id,
            'payload' => $order->payload ?? [],
            'isPaid' => $order->is_paid,
            'prices' => $orderPrice->toArray(),
        ]);

        event(new OrderCreatedEvent(orderDTO: $orderDto));

        return $orderDto;
    }

    public function calcPrice(BookingRequestDTO $bookingRequestDTO): OrderPriceDTO {
        $priceRow = PriceHistory::query()->where('price_id', $bookingRequestDTO->priceId)->first();

        if (!$priceRow) {
            abort(code: 404, message: 'Прайс не найден');
        }

        $price = $priceRow->price;

        // ищем сохраненную услугу по id
        $servicePrice = 0;
        foreach ($bookingRequestDTO->services as $service) {
            $service = Service::query()->where("service_id", $service->id)->first();

            if (!$service) {
                abort(code: 404, message: "Услуга не найдена. Невозможно рассчитать стоимость");
            }

            $servicePrice += $service->price;
        }

        return OrderPriceDTO::fromArray(data: [
           'fullPrice' => $price['price'] + $servicePrice,
           'tripPrice' => $price['price'],
           'servicePrice' => $servicePrice,
        ]);
    }
}
