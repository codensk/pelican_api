<?php

namespace App\DTO;

use Carbon\Carbon;

class FlightDTO
{
    public function __construct(
        public ?string $flightNumber,
        public ?Carbon $flightDateTime
    ) {}

    public function toArray(): array
    {
        return [
            'flightNumber' => $this->flightNumber,
            'flightDateTime' => $this->flightDateTime?->format("Y-m-d H:i") ?? null,
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            flightNumber: $data['flightNumber'],
            flightDateTime: ($data['flightDateTime'] ?? null) ? Carbon::parse($data['flightDateTime']) : null,
        );
    }
}
