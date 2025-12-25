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
        public ?float $priceUsd,
        public ?float $priceRefundableTicketUsd,
        public ?float $refundableTicketPercent,
        public ?string $currency, // RUB / USD / EUR
        public ?float $currencyUsdValue,
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
            'priceUsd' => $this->priceUsd,
            'priceRefundableTicketUsd' => $this->priceRefundableTicketUsd,
            'refundableTicketPercent' => $this->refundableTicketPercent,
            'currency' => $this->currency,
            'currencyUsdValue' => $this->currencyUsdValue,
            'pickupPlaceType' => $this->pickupPlaceType->value ?? null,
            'dropoffPlaceType' => $this->dropoffPlaceType->value ?? null,
        ];
    }

    public static function fromArray(array $data): self
    {
        $currencyUsdValue = $data['currencyUsdValue'] ?? null;
        $priceRub = $data['price'] ?? null;
        $priceRefundableTicketRub = $data['priceRefundableTicket'] ?? null;

        $priceUsd = $priceRub > 0 && $currencyUsdValue > 0 ? round($priceRub / $currencyUsdValue, 2) : null;
        $priceRefundableTicketUsd = $priceRub > 0 && $currencyUsdValue > 0 ? round($priceRefundableTicketRub / $currencyUsdValue, 2) : null;

        return new self(
            priceId: $data['priceId'],
            vehicleClassId: $data['vehicleClassId'],
            maxPassengers: $data['maxPassengers'],
            distance: $data['distance'],
            duration: $data['duration'],
            price: $priceRub,
            priceRefundableTicket: $priceRefundableTicketRub,
            priceUsd: $priceUsd,
            priceRefundableTicketUsd: $priceRefundableTicketUsd,
            refundableTicketPercent: $data['refundableTicketPercent'] ?? 0,
            currency: $data['currency'] ?? null,
            currencyUsdValue: $data['currencyUsdValue'] ?? null,
            pickupPlaceType: ($data['pickupPlaceTypeId'] ?? null) ? PlaceTypeEnum::byId(id: $data['pickupPlaceTypeId'] ?? null) : null,
            dropoffPlaceType: ($data['dropoffPlaceTypeId'] ?? null) ? PlaceTypeEnum::byId(id: $data['dropoffPlaceTypeId'] ?? null) : null,
        );
    }
}
