<?php

namespace App\DTO;

use App\Services\Enums\UserRoleEnum;
use Illuminate\Support\Carbon;

class PriceRequestDTO
{
    public function __construct(
        public string $token,
        public int $contractId,
        public string $pickupLocation,
        public string $dropoffLocation,
        public Carbon $pickupAt,
        public float $pickupLat,
        public float $pickupLon,
        public float $dropoffLat,
        public float $dropoffLon,
    ) {}

    public function toArray(): array
    {
        return [
            'token' => $this->token,
            'contractId' => $this->contractId,
            'pickupLocation' => $this->pickupLocation,
            'dropoffLocation' => $this->dropoffLocation,
            'pickupAt' => $this->pickupAt->format("Y-m-d H:i"),
            'pickupLat' => $this->pickupLat,
            'pickupLon' => $this->pickupLon,
            'dropoffLat' => $this->dropoffLat,
            'dropoffLon' => $this->dropoffLon,
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            token: $data['token'],
            contractId: $data['contractId'],
            pickupLocation: $data['pickupLocation'],
            dropoffLocation: $data['dropoffLocation'],
            pickupAt: Carbon::parse($data['pickupAt']),
            pickupLat: $data['pickupLat'],
            pickupLon: $data['pickupLon'],
            dropoffLat: $data['dropoffLat'],
            dropoffLon: $data['dropoffLon'],
        );
    }
}
