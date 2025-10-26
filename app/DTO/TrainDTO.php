<?php

namespace App\DTO;

use Carbon\Carbon;

class TrainDTO
{
    public function __construct(
        public ?string $trainNumber,
        public ?string $trainCarriage,
        public ?Carbon $trainDateTime
    ) {}

    public function toArray(): array
    {
        return [
            'trainNumber' => $this->trainNumber,
            'trainCarriage' => $this->trainCarriage,
            'trainDateTime' => $this->trainDateTime?->format("Y-m-d H:i") ?? null,
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            trainNumber: $data['trainNumber'],
            trainCarriage: $data['trainCarriage'],
            trainDateTime: ($data['trainDateTime'] ?? null) ? Carbon::parse($data['trainDateTime']) : null,
        );
    }
}
