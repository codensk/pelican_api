<?php

namespace App\Services;

use App\DTO\BookingRequestDTO;
use App\DTO\OrderDTO;
use App\DTO\OrderPriceDTO;
use App\Events\OrderCreatedEvent;
use App\Events\OrderSendFailedEvent;
use App\Models\Order;
use App\Models\PriceHistory;
use App\Models\Service;
use App\Services\Enums\TicketTypeEnum;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
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

    public function sendOrderToBooking(string $orderId): void {
        $order = Order::query()->where("order_id", $orderId)->first();

        if (!$order) {
            event(new OrderSendFailedEvent(orderId: $orderId));

            return;
        }

        $clientData = $this->clientTokenService->getClientData(setUserId: $order->user_id);

        $orderDto = $order->toDto();

        $req = Http::retry(times: 3, sleepMilliseconds: 100, throw: false)
            ->timeout(seconds: 60)
            ->withToken(token: $clientData['token'])
            ->post(config("services.booking.endpoints.tripEndpoint"), $this->prepareTripsPayload(order: $orderDto, employeeId: $clientData['employeeId']));

        /**
         * todo:
         * - отправить письмо что надо оплатить (ссылка на оплату)
         * - после оплаты отправить письмо что оплачено
         * - взять ссылку на ваучер
         * - отправить письмо с ваучером клиенту на email
         * - отправить в букинг письмо о том, что заказ оформлен
         * - обработка ивентов по ошибкам
         * - удалить прайс
         * - пометить заказ оплаченным
         * - сохранить ссылку на ваучер
         *
         */

        dd($req->body());
    }

    private function prepareTripsPayload(OrderDTO $order, ?int $employeeId): array {
        $payload = $order->payload;

        $passengers = [
            [
                'name' => $payload['passenger']['firstName'] . ' ' . $payload['passenger']['lastName'],
                'phone' => $payload['passenger']['phone'],
                'additionalPhone' => $payload['passenger']['secondaryPhone'],
                'sms' => false,
            ]
        ];

        $pickup = [];

        if ($payload['pickupLocation']['train'] ?? false) {
            $pickup['railwayDetails'] = [
                'train' => $payload['pickupLocation']['train']['trainNumber'] ?? null,
                'wagon' => $payload['pickupLocation']['train']['trainCarriage'] ?? null,
                'trainDate' => $payload['pickupLocation']['train']['trainDateTime'] ?? null,
            ];
        }

        if ($payload['pickupLocation']['flight'] ?? false) {
            $pickup['airport_details'] = [
                'flightNumber' => $payload['pickupLocation']['flight']['flightNumber'] ?? null,
                'flightDate' => $payload['pickupLocation']['flight']['flightDateTime'] ?? null,
            ];
        }

        $dropoff = [];

        if ($payload['dropoffLocation']['train'] ?? false) {
            $dropoff['railwayDetails'] = [
                'train' => $payload['dropoffLocation']['train']['trainNumber'] ?? null,
                'wagon' => $payload['dropoffLocation']['train']['trainCarriage'] ?? null,
                'trainDate' => $payload['dropoffLocation']['train']['trainDateTime'] ?? null,
            ];
        }

        if ($payload['dropoffLocation']['flight'] ?? false) {
            $dropoff['airport_details'] = [
                'flightNumber' => $payload['dropoffLocation']['flight']['flightNumber'] ?? null,
                'flightDate' => $payload['dropoffLocation']['flight']['flightDateTime'] ?? null,
            ];
        }

        $trips = [
            [
                'entryId' => $order->priceId,
                'clientEmployerId' => $employeeId,
                'showPriceInVoucher' => false,
                'lang' => 'ru',
                'voucherLang' => 'ru',
                'passengers' => $passengers,
                'pickup' => $pickup,
                'dropoff' => $dropoff,
            ]
        ];

        return [
            'trips' => $trips
        ];
    }
}
