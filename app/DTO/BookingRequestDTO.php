<?php

namespace App\DTO;

use App\Services\Enums\TicketTypeEnum;

class BookingRequestDTO
{
    public function __construct(
        public string $priceId,
        public LocationDetailsDTO $pickupLocation,
        public LocationDetailsDTO $dropoffLocation,

        public PassengerDTO $passenger,
        public ?string $driverComment,

        /**
         * @var ServiceDTO[]
         */
        public ?array $services,
        public ?TicketTypeEnum $ticketType,
    ) {}

    public function toArray(): array
    {
        $services = [];

        foreach($this->services ?? [] as $service) {
            $services[] = $service->toArray();
        }

        return [
            'priceId' => $this->priceId,
            'pickupLocation' => $this->pickupLocation?->toArray() ?? null,
            'dropoffLocation' => $this->dropoffLocation?->toArray() ?? null,
            'driverComment' => $this->driverComment,
            'passenger' => $this->passenger?->toArray() ?? null,
            'services' => $services,
            'ticketType' => $this->ticketType->value ?? null,
        ];
    }

    public static function fromArray(array $data): self
    {
        $services = [];

        foreach($data['services'] ?? [] as $service) {
            $services[] = ServiceDTO::fromArray(data: $service);
        }

        return new self(
            priceId: $data['priceId'],
            pickupLocation: ($data['pickupLocation'] ?? null) ? LocationDetailsDTO::fromArray(data: $data['pickupLocation']) : null,
            dropoffLocation: ($data['dropoffLocation'] ?? null) ? LocationDetailsDTO::fromArray(data: $data['dropoffLocation']) : null,
            passenger: ($data['passenger'] ?? null) ? PassengerDTO::fromArray(data: $data['passenger']) : null,
            driverComment: $data['driverComment'] ?? null,
            services: $services,
            ticketType: ($data['ticketType'] ?? null) ? TicketTypeEnum::tryFrom(value: $data['ticketType']) : null,
        );
    }
}
