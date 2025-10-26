<?php

namespace App\DTO;

class PlaceDTO
{
    public function __construct(
        public ?string $name,
        public ?string $address,
        public ?float $lat,
        public ?float $lon,
    ) {}

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'address' => $this->address,
            'lat' => $this->lat,
            'lon' => $this->lon,
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            address: $data['address'],
            lat: $data['lat'],
            lon: $data['lon'],
        );
    }
}
