<?php

namespace App\DTO;

class PriceResultDTO
{
    public function __construct(
        public string $priceId,
        public int $vehicleClassId,
        public int $maxPassengers,
        public ?float $distance,
        public ?string $duration,
        public ?float $price,
        public ?string $currency, // RUB / USD / EUR
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
            'currency' => $this->currency,
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
            currency: $data['currency'] ?? null,
        );
    }
}
