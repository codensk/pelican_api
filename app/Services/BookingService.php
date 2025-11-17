<?php

namespace App\Services;

use App\DTO\BookingRequestDTO;
use App\DTO\OrderDTO;
use App\DTO\OrderPriceDTO;
use App\DTO\ServiceDTO;
use App\Events\OrderBookingFailedEvent;
use App\Events\OrderBookingSuccessEvent;
use App\Events\OrderCreatedEvent;
use App\Events\OrderSendFailedEvent;
use App\Jobs\SendVoucherJob;
use App\Mail\SendVoucher;
use App\Models\Order;
use App\Models\PriceHistory;
use App\Models\Service;
use App\Models\User;
use App\Services\Enums\TicketTypeEnum;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Storage;

readonly class BookingService
{
    public function __construct(
        private ClientTokenService $clientTokenService,
        private SearchService $searchService,
        private PriceHistoryService $priceHistoryService,
        private PaymentService $paymentService,
        private ServiceManager $serviceManager,
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
        $priceHistoryRow = $this->priceHistoryService->fetchById(priceId: $bookingRequestDTO->priceId);

        $orderId = date("dmY") . Order::query()->count() + 1;

        $bookingRequestDTO = $this->checkBookingRequest(bookingRequestDTO: $bookingRequestDTO);

        $payload = $bookingRequestDTO->toArray();

        $order = Order::query()->create([
            'user_id' => $userId,
            'code' => "pn" . Str::random(98),
            'notification_email' => $this->getNotificationEmail(userId: $userId, orderPayload: $payload),
            'price_id' => $bookingRequestDTO->priceId,
            'vehicle_class_id' => $priceHistoryRow->price['vehicleClassId'] ?? null,
            'full_price' => $orderPrice->fullPrice,
            'full_price_refundable' => $orderPrice->fullPriceRefundable,
            'order_price' => $orderPrice->tripPrice,
            'services_price' => $orderPrice->servicePrice,
            'order_id' => "PN-{$orderId}",
            'payload' => $payload,
            'additional_stops' => $bookingRequestDTO->additionalStops,
            'price_payload' => $priceHistoryRow->payload ?? null,
            'is_paid' => false,
            'expires_at' => now()->addHours(6),
            'is_refundable' => $bookingRequestDTO->ticketType == TicketTypeEnum::Refundable,
            'refundable_ticket_percent' => $clientData['refundableTicketPercent'],
        ]);

        $orderDto = OrderDTO::fromArray(data: [
            'userId' => $order->user_id,
            'code' => $order->code,
            'notificationEmail' => $order->notification_email,
            'priceId' => $order->price_id,
            'vehicleClassId' => $order->vehicle_class_id,
            'orderId' => $order->order_id,
            'payload' => $order->payload ?? [],
            'additionalStops' => $order->additionalStops ?? [],
            'pricePayload' => $priceHistoryRow->payload ?? null,
            'expiresAt' => $order->expires_at,
            'isPaid' => $order->is_paid,
            'isRefundable' => $order->is_refundable,
            'refundableTicketPercent' => $order->refundable_ticket_percent,
            'prices' => $orderPrice->toArray(),
            'createdAt' => $order->created_at,
        ]);

        event(new OrderCreatedEvent(orderDTO: $orderDto));

        return $orderDto;
    }

    /**
     * Если указаны доп заезды, то нужно скорректировать в доп. услугах соответствующую услугу (количество), чтобы не было ситуации, когда, например, указан доп заезд один, а по факту в заказе их 3
     *
     * @param BookingRequestDTO $bookingRequestDTO
     * @return BookingRequestDTO|null
     */
    public function checkBookingRequest(BookingRequestDTO $bookingRequestDTO): ?BookingRequestDTO {
        $found = false;

        $additionalStopsCount = count($bookingRequestDTO->additionalStops);

        foreach ($bookingRequestDTO->services ?? [] as $idx => $service) {
            if ($service->serviceCode == 'additional_stop') {
                $bookingRequestDTO->services[$idx]->quantity = $additionalStopsCount;
                $found = true;
                break;
            }
        }

        // если не нашли - добавляем как новую услугу
        if (!$found && $additionalStopsCount > 0) {
            $service = Service::query()->where("service_code", "additional_stop")->first();

            $bookingRequestDTO->services[] = ServiceDTO::fromArray(data: [
                'id' => $service->id,
                'quantity' => $additionalStopsCount,
            ]);
        }

        return $bookingRequestDTO;
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

        try {
            $req = Http::retry(times: 3, sleepMilliseconds: 100, throw: false)
                ->timeout(seconds: 60)
                ->withToken(token: $clientData['token'])
                ->post(config("services.booking.endpoints.tripEndpoint"), $this->prepareTripsPayload(order: $orderDto, employeeId: $clientData['employeeId']));
        } catch (\Exception $exception) {
            Log::error("OrderID: {$orderId}");
            Log::error($exception->getMessage());

            event(new OrderBookingFailedEvent(orderDTO: $orderDto, reason: $exception->getMessage()));

            return;
        }

        $json = $req->json();

        if (!($json[0]['tripId'] ?? false)) {
            // заказ не оформился

            event(new OrderBookingFailedEvent(orderDTO: $orderDto, reason: json_encode(value: $json, flags: JSON_UNESCAPED_UNICODE)));

            return;
        }

        // заказ успешно оформлен
        event(new OrderBookingSuccessEvent(orderDTO: $orderDto));
    }

    public function sendVoucher(OrderDTO $order): void {
        dispatch(job: new SendVoucherJob(email: $order->notificationEmail, orderDTO: $order));
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

        $tripBackendComment = $this->makeBackendComment(order: $order);

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
                'isNeedPlate' => (bool) ($payload['plateText'] ?? null),
                'plateText' => $payload['plateText'] ?? null,
                'additionalAddresses' => $order->additionalStops ?? [],
                'note' => $payload['driverComment'] ?? null,
                'tripBackendComment' => $tripBackendComment,
            ]
        ];

        return [
            'trips' => $trips
        ];
    }

    /**
     * Возвращает текст внутреннего комментария который уходит вместе с заказом.
     * Внутри комментария указываются некоторые детали заказа и выбранные услуги
     *
     * @param OrderDTO $order
     * @return string
     */
    private function makeBackendComment(OrderDTO $order): string {
        $serviceLines = [];
        $orderInfoLines = [];

        $payload = $order->payload ?? [];

        // информация об услугах
        foreach($payload['services'] ?? [] as $service) {
            $serviceDTO = $this->serviceManager->fetchById(id: $service['id']);

            if ($serviceDTO) {
                $servicePrice = $serviceDTO->price;
                $serviceFullPrice = $service['quantity'] * $serviceDTO->price;
                $serviceLines[] = "👉 {$serviceDTO->title} x {$service['quantity']} = {$servicePrice} * {$service['quantity']} = {$serviceFullPrice} {$serviceDTO->currency}";
            }
        }

        // информация о заказе
        $paidSum = $order->isRefundable ? $order->prices->fullPriceRefundable : $order->prices->fullPrice;
        $ticketType = $order->isRefundable ? 'Возвратный' : 'Не возвратный';
        $orderInfoLines[] = "Заказ: {$order->orderId}";
        $orderInfoLines[] = "Полная стоимость: {$paidSum} RUB";
        $orderInfoLines[] = "Оплачено: {$paidSum} RUB";
        $orderInfoLines[] = "Тип билета: {$ticketType}";
        $orderInfoLines[] = "E-mail для уведомлений: {$order->notificationEmail}";
        $orderInfoLines[] = "--------------------";

        return implode("\n", $orderInfoLines) . "\n" . implode("\n", $serviceLines);
    }

    /**
     * Возвращает e-mail для отправки уведомлений, ссылок на оплату
     *
     * @param int|null $userId
     * @param array $orderPayload
     * @return string|null
     */
    private function getNotificationEmail(?int $userId, array $orderPayload): ?string {
        if ($userId) {
            $user = User::query()->where("id", $userId)->first();

            if ($user->email ?? false) {
                return $user->email;
            }
        }

        if ($orderPayload['passenger']['email'] ?? false) {
            return $orderPayload['passenger']['email'];
        }

        return null;
    }
}
