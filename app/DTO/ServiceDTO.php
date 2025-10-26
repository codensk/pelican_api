<?php

namespace App\DTO;

use App\Services\Enums\UserRoleEnum;

class ServiceDTO
{
    public function __construct(
        public int $id,
        public string $title,
        public ?string $description,
        public ?float $price,
        public ?string $currency, // валюта стоимости
        public ?bool $isCountable, // можно менять количество
        public int $quantity, // количество
        public ?bool $defaultState, // дефолтное состояние услуги (например, какая то услуга должна быть по умолчанию выбрана в UI)
    ) {}

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'price' => $this->price,
            'currency' => $this->currency,
            'defaultState' => $this->defaultState,
            'isCountable' => $this->isCountable,
            'quantity' => $this->quantity,
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            title: $data['title'],
            description: $data['description'] ?? null,
            price: $data['price'] ?? null,
            currency: $data['currency'] ?? null,
            isCountable: $data['isCountable'] ?? null,
            quantity: $data['quantity'] ?? 1,
            defaultState: $data['defaultState'] ?? null,
        );
    }
}
