<?php

namespace App\DTO;

class LocationDetailsDTO
{
    public function __construct(
        public ?FlightDTO $flight,
        public ?TrainDTO $train,
    ) {}

    public function toArray(): array
    {
        return [
            'flight' => $this->flight?->toArray() ?? null,
            'train' => $this->train?->toArray() ?? null,
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            flight: ($data['flight'] ?? null) ? FlightDTO::fromArray(data: $data['flight']) : null,
            train: ($data['train'] ?? null) ? TrainDTO::fromArray(data: $data['train']) : null,
        );
    }
}
