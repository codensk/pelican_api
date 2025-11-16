<?php

namespace App\DTO;

use App\Services\Enums\TicketTypeEnum;
use App\Services\Enums\VehicleClassEnum;
use App\Services\ServiceManager;
use Carbon\Carbon;

class OrderDTO
{
    public function __construct(
        public ?int $userId,
        public ?string $code = null,
        public ?string $notificationEmail,
        public string $priceId,
        public ?int $vehicleClassId,
        public string $orderId,
        public float $refundableTicketPercent,
        public array $payload,
        public ?array $additionalStops = [],
        public array $pricePayload,
        public ?string $paymentLink,
        public Carbon $expiresAt,
        public bool $isPaid,
        public bool $isRefundable,
        public OrderPriceDTO $prices,
        public ?Carbon $createdAt = null,
    ) {}

    public function toArray(): array
    {
        return [
            'userId' => $this->userId,
            'code' => $this->code,
            'notificationEmail' => $this->notificationEmail,
            'priceId' => $this->priceId,
            'vehicleClassId' => $this->vehicleClassId,
            'orderId' => $this->orderId,
            'payload' => $this->payload,
            'additionalStops' => $this->additionalStops ?? [],
            'pricePayload' => $this->pricePayload,
            'isPaid' => $this->isPaid,
            'paymentLink' => $this->paymentLink,
            'expiresAt' => $this->expiresAt,
            'refundableTicketPercent' => $this->refundableTicketPercent,
            'isRefundable' => $this->isRefundable,
            'prices' => $this->prices->toArray(),
            'createdAt' => $this->createdAt,
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            userId: $data['userId'],
            code: $data['code'] ?? null,
            notificationEmail: $data['notificationEmail'],
            priceId: $data['priceId'],
            vehicleClassId: $data['vehicleClassId'],
            orderId: $data['orderId'],
            refundableTicketPercent: $data['refundableTicketPercent'],
            payload: $data['payload'],
            additionalStops: $data['additionalStops'] ?? [],
            pricePayload: $data['pricePayload'],
            paymentLink: $data['paymentLink'] ?? null,
            expiresAt: ($data['expiresAt'] ?? null) ? Carbon::parse($data['expiresAt']) : null,
            isPaid: $data['isPaid'],
            isRefundable: $data['isRefundable'],
            prices: OrderPriceDTO::fromArray($data['prices']),
            createdAt: ($data['prices'] ?? null) ? Carbon::parse($data['createdAt']) : null,
        );
    }

    public function getCarClassName(): ?string {
        return VehicleClassEnum::byId($this->vehicleClassId)?->value ?? null;
    }

    public function getPickupLocation(): string {
        $pricePayload = $this->pricePayload;

        $pickupLocation = [];

        if ($pricePayload['pickupLocation']['name'] ?? false) {
            $pickupLocation[] = $pricePayload['pickupLocation']['name'];
        }

        if ($pricePayload['pickupLocation']['address'] ?? false) {
            $pickupLocation[] = $pricePayload['pickupLocation']['address'];
        }

        return implode(", ", $pickupLocation);
    }

    public function getDropoffLocation(): string {
        $pricePayload = $this->pricePayload;

        $dropoffLocation = [];

        if ($pricePayload['dropoffLocation']['name'] ?? false) {
            $dropoffLocation[] = $pricePayload['dropoffLocation']['name'];
        }

        if ($pricePayload['dropoffLocation']['address'] ?? false) {
            $dropoffLocation[] = $pricePayload['dropoffLocation']['address'];
        }

        return implode(", ", $dropoffLocation);
    }

    public function getPickupDate(): ?string {
        $pricePayload = $this->pricePayload;

        $pickupAt = $pricePayload['pickupAt'] ?? null;

        return $pickupAt ? Carbon::parse($pickupAt)->format('d.m.Y') : null;
    }

    public function getPickupTime(): ?string {
        $pricePayload = $this->pricePayload;

        $pickupAt = $pricePayload['pickupAt'] ?? null;

        return $pickupAt ? Carbon::parse($pickupAt)->format('H:i') : null;
    }

    public function getNumberOfPassengers(): int {
        return $this->payload['passenger']['numberOfPassengers'] ?? 1;
    }

    public function getPassengerName(): string {
        $passengerName = [];

        if ($this->payload['passenger'] ?? false) {
            if ($this->payload['passenger']['firstName'] ?? false) {
                $passengerName[] = $this->payload['passenger']['firstName'];
            }

            if ($this->payload['passenger']['lastName'] ?? false) {
                $passengerName[] = $this->payload['passenger']['lastName'];
            }
        }

        return implode(" ", $passengerName);
    }

    public function getPassengerPhone(): string {
        $passengerPhones = [];

        if ($this->payload['passenger'] ?? false) {
            if ($this->payload['passenger']['phone'] ?? false) {
                $passengerPhones[] = $this->payload['passenger']['phone'];
            }

            if ($this->payload['passenger']['secondaryPhone'] ?? false) {
                $passengerPhones[] = $this->payload['passenger']['secondaryPhone'];
            }
        }

        return implode(", ", $passengerPhones);
    }

    public function getPassengerEmail(): ?string {
        return $this->payload['passenger']['email'] ?? null;
    }

    public function getDriverComment(): ?string {
        return $this->payload['driverComment'] ?? null;
    }

    public function getServicePrices(): array {
        $serviceManager = app(ServiceManager::class);

        $servicesTable = [];

        $servicesTable[] = [
            'serviceName' => "Трансфер",
            'servicePrice' => $this->prices->tripPrice,
        ];

        foreach($this->payload['services'] ?? [] as $service) {
            $serviceDTO = $serviceManager->fetchById(id: $service['id']);

            if ($serviceDTO) {
                $serviceFullPrice = $service['quantity'] * $serviceDTO->price;

                $servicesTable[] = [
                    'serviceName' => $serviceDTO->title,
                    'servicePrice' => $serviceFullPrice,
                ];
            }
        }

        return $servicesTable;
    }

    public function getAdditionalStops(): array {
        return $this->additionalStops;
    }
}
