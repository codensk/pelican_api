<?php

namespace App\Services;

use App\DTO\BookingRequestDTO;
use App\DTO\OrderDTO;
use App\DTO\OrderPriceDTO;
use App\Events\OrderCreatedEvent;
use App\Models\Order;
use App\Models\PriceHistory;
use App\Models\Service;
use App\Services\Enums\TicketTypeEnum;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

readonly class BookingService
{
    public function __construct(
        private ClientTokenService $clientTokenService,
        private SearchService $searchService,
        private PaymentService $paymentService,
    ) {}

    /**
     * Метод сохраняет заказ в базе и возвращает ссылку на оплату
     */
    public function processOrder(array $data): array {
        $bookingRequestDTO = BookingRequestDTO::fromArray(data: $data);

        $order = $this->saveOrder(bookingRequestDTO: $bookingRequestDTO, userId: Auth::guard('api')->user()->id ?? null);

        return $this->paymentService->createPayment(order: $order);
    }


    /**
     * Сохраняет заказ в базе до оплаты
     *
     * @param BookingRequestDTO $bookingRequestDTO
     * @param int|null $userId
     * @return OrderDTO
     */
    public function saveOrder(BookingRequestDTO $bookingRequestDTO, ?int $userId = null): OrderDTO {
        $clientData = $this->clientTokenService->getClientData();
        $orderPrice = $this->calcPrice(bookingRequestDTO: $bookingRequestDTO, refundableTicketPercent: $clientData['refundableTicketPercent']);

        $orderId = Str::random(length: 10);

        $order = Order::query()->create([
            'user_id' => $userId,
            'price_id' => $bookingRequestDTO->priceId,
            'full_price' => $orderPrice->fullPrice,
            'full_price_refundable' => $orderPrice->fullPriceRefundable,
            'order_price' => $orderPrice->tripPrice,
            'services_price' => $orderPrice->servicePrice,
            'order_id' => "PN-{$orderId}",
            'payload' => $bookingRequestDTO->toArray(),
            'is_paid' => false,
            'expires_at' => now()->addHours(6),
            'is_refundable' => $bookingRequestDTO->ticketType == TicketTypeEnum::Refundable,
            'refundable_ticket_percent' => $clientData['refundableTicketPercent'],
        ]);

        $orderDto = OrderDTO::fromArray(data: [
            'userId' => $order->user_id,
            'priceId' => $order->price_id,
            'orderId' => $order->order_id,
            'payload' => $order->payload ?? [],
            'expiresAt' => $order->expires_at,
            'isPaid' => $order->is_paid,
            'isRefundable' => $order->is_refundable,
            'refundableTicketPercent' => $order->refundable_ticket_percent,
            'prices' => $orderPrice->toArray(),
        ]);

        event(new OrderCreatedEvent(orderDTO: $orderDto));

        return $orderDto;
    }

    public function calcPrice(BookingRequestDTO $bookingRequestDTO, ?float $refundableTicketPercent = 0): OrderPriceDTO {
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

        $fullPrice = $price['price'] + $servicePrice;

        $priceRefundableTicket = $this->searchService->calcRefundableTicketPrice(ticketPrice: $fullPrice, refundableTicketPercent: $refundableTicketPercent);

        return OrderPriceDTO::fromArray(data: [
           'fullPrice' => $fullPrice,
           'fullPriceRefundable' => $priceRefundableTicket,
           'tripPrice' => $price['price'],
           'servicePrice' => $servicePrice,
        ]);
    }
}
