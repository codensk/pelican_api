<?php

namespace App\DTO;

class OrderPriceDTO
{
    public function __construct(
        public float $fullPrice, // стоимость трипа + доп услуг
        public float $fullPriceRefundable, // полная стоимость заказа с учетом возвратного билета
        public float $tripPrice, // стоимость трипа
        public float $servicePrice // стоимость доп услуг
    ) {}

    public function toArray(): array
    {
        return [
            'fullPrice' => $this->fullPrice,
            'fullPriceRefundable' => $this->fullPriceRefundable,
            'tripPrice' => $this->tripPrice,
            'servicePrice' => $this->servicePrice,
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            fullPrice: $data['fullPrice'],
            fullPriceRefundable: $data['fullPriceRefundable'],
            tripPrice: $data['tripPrice'],
            servicePrice: $data['servicePrice'],
        );
    }
}
