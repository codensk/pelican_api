<?php

namespace App\DTO;

class OrderPriceDTO
{
    public function __construct(
        public float $fullPrice, // стоимость трипа + доп услуг
        public float $tripPrice, // стоимость трипа
        public float $servicePrice // стоимость доп услуг
    ) {}

    public function toArray(): array
    {
        return [
            'fullPrice' => $this->fullPrice,
            'tripPrice' => $this->tripPrice,
            'servicePrice' => $this->servicePrice,
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            fullPrice: $data['fullPrice'],
            tripPrice: $data['tripPrice'],
            servicePrice: $data['servicePrice'],
        );
    }
}
