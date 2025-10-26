<?php

namespace App\DTO;

use App\Services\Enums\TicketTypeEnum;

class BookingRequestDTO
{
    public function __construct(
        public string $priceId,
        public LocationDetailsDTO $pickupLocation,
        public LocationDetailsDTO $dropoffLocation,

        /**
         * @var PassengerDTO[]
         */
        public ?array $passenger,
        public ?string $driverComment,

        /**
         * @var ServiceDTO[]
         */
        public ?array $services,
        public ?TicketTypeEnum $ticketType,
    ) {}

    public function toArray(): array
    {
        return [
            'priceId' => $this->priceId,
            'pickupLocation' => $this->pickupLocation?->toArray() ?? null,
            'dropoffLocation' => $this->dropoffLocation?->toArray() ?? null,
            'driverComment' => $this->driverComment,
            'services' => $this->services,
            'ticketType' => $this->ticketType,
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            priceId: $data['priceId'],
            pickupLocation: ($data['pickupLocation'] ?? null) ? LocationDetailsDTO::fromArray(data: $data['pickupLocation']) : null,
            dropoffLocation: ($data['dropoffLocation'] ?? null) ? LocationDetailsDTO::fromArray(data: $data['dropoffLocation']) : null,
            passenger: ($data['passenger'] ?? null) ? PassengerDTO::fromArray(data: $data['passenger']) : null,
            driverComment: $data['driverComment'] ?? null,
            services: ($data['services'] ?? null) ? ServiceDTO::fromArray(data: $data['services']) : null,
            ticketType: ($data['ticketType'] ?? null) ? TicketTypeEnum::tryFrom(value: $data['ticketType']) : null,
        );
    }
}
