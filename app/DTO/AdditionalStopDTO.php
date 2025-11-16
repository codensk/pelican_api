<?php

namespace App\DTO;

class AdditionalStopDTO
{
    public function __construct(
        public string $address,
        public ?string $comment,
        public string $lat,
        public string $lon,
    ) {}

    public function toArray(): array
    {
        return [
            'address' => $this->address,
            'comment' => $this->comment,
            'lat' => $this->lat,
            'lon' => $this->lon,
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            address: $data['address'],
            comment: $data['comment'] ?? null,
            lat: $data['lat'],
            lon: $data['lon'],
        );
    }
}
