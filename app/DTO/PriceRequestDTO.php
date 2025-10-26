<?php

namespace App\DTO;

use App\Services\Enums\UserRoleEnum;
use Illuminate\Support\Carbon;

class PriceRequestDTO
{
    public function __construct(
        public string $token,
        public int $contractId,
        public PlaceDTO $pickupLocation,
        public PlaceDTO $dropoffLocation,
        public Carbon $pickupAt
    ) {}

    public function toArray(): array
    {
        return [
            'token' => $this->token,
            'contractId' => $this->contractId,
            'pickupLocation' => $this->pickupLocation->toArray(),
            'dropoffLocation' => $this->dropoffLocation->toArray(),
            'pickupAt' => $this->pickupAt->format("Y-m-d H:i"),
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            token: $data['token'],
            contractId: $data['contractId'],
            pickupLocation: is_array($data['pickupLocation']) ? PlaceDTO::fromArray(data: $data['pickupLocation']) : $data['pickupLocation'],
            dropoffLocation: is_array($data['dropoffLocation']) ? PlaceDTO::fromArray(data: $data['dropoffLocation']) : $data['dropoffLocation'],
            pickupAt: Carbon::parse($data['pickupAt'])
        );
    }
}
