<?php

namespace App\DTO;

use App\Services\Enums\UserRoleEnum;

class ServiceDTO
{
    public function __construct(
        public int $id,
        public ?string $title,
        public ?string $description,
        public ?float $price,
        public ?string $group, // группа услуг (обычная услуга или услуга связи)
        public ?string $serviceCode, // код услуги (чтобы различать)
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
            'group' => $this->group,
            'serviceCode' => $this->serviceCode,
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
            title: $data['title'] ?? null,
            description: $data['description'] ?? null,
            price: $data['price'] ?? null,
            group: $data['group'] ?? null,
            serviceCode: $data['serviceCode'] ?? null,
            currency: $data['currency'] ?? null,
            isCountable: $data['isCountable'] ?? null,
            quantity: $data['quantity'] ?? 1,
            defaultState: $data['defaultState'] ?? null,
        );
    }
}
