<?php

namespace App\DTO;

use App\Services\Enums\PlaceTypeEnum;

class PriceResultDTO
{
    public function __construct(
        public string $priceId,
        public int $vehicleClassId,
        public int $maxPassengers,
        public ?float $distance,
        public ?string $duration,
        public ?float $price,
        public ?float $priceRefundableTicket,
        public ?float $refundableTicketPercent,
        public ?string $currency, // RUB / USD / EUR
        public ?PlaceTypeEnum $pickupPlaceType,
        public ?PlaceTypeEnum $dropoffPlaceType,
    ) {}

    public function toArray(): array
    {
        return [
            'priceId' => $this->priceId,
            'vehicleClassId' => $this->vehicleClassId,
            'maxPassengers' => $this->maxPassengers,
            'distance' => $this->distance,
            'duration' => $this->duration,
            'price' => $this->price,
            'priceRefundableTicket' => $this->priceRefundableTicket,
            'refundableTicketPercent' => $this->refundableTicketPercent,
            'currency' => $this->currency,
            'pickupPlaceType' => $this->pickupPlaceType->value ?? null,
            'dropoffPlaceType' => $this->dropoffPlaceType->value ?? null,
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            priceId: $data['priceId'],
            vehicleClassId: $data['vehicleClassId'],
            maxPassengers: $data['maxPassengers'],
            distance: $data['distance'],
            duration: $data['duration'],
            price: $data['price'] ?? null,
            priceRefundableTicket: $data['priceRefundableTicket'] ?? null,
            refundableTicketPercent: $data['refundableTicketPercent'] ?? 0,
            currency: $data['currency'] ?? null,
            pickupPlaceType: ($data['pickupPlaceTypeId'] ?? null) ? PlaceTypeEnum::byId(id: $data['pickupPlaceTypeId'] ?? null) : null,
            dropoffPlaceType: ($data['dropoffPlaceTypeId'] ?? null) ? PlaceTypeEnum::byId(id: $data['dropoffPlaceTypeId'] ?? null) : null,
        );
    }
}
