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
        public ?string $plateText,

        /**
         * @var ServiceDTO[]
         */
        public ?array $services,

        /**
         * @var AdditionalStopDTO[]
         */
        public ?array $additionalStops,
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
            'plateText' => $this->plateText,
            'passenger' => $this->passenger?->toArray() ?? null,
            'services' => $services,
            'additionalStops' => $this->additionalStops ?? [],
            'ticketType' => $this->ticketType->value ?? null,
        ];
    }

    public static function fromArray(array $data): self
    {
        $services = [];
        $additionalStops = [];

        foreach($data['services'] ?? [] as $service) {
            $services[] = ServiceDTO::fromArray(data: $service);
        }

        foreach($data['additionalStops'] ?? [] as $additionalStop) {
            $additionalStops[] = AdditionalStopDTO::fromArray(data: $additionalStop);
        }

        return new self(
            priceId: $data['priceId'],
            pickupLocation: ($data['pickupLocation'] ?? null) ? LocationDetailsDTO::fromArray(data: $data['pickupLocation']) : null,
            dropoffLocation: ($data['dropoffLocation'] ?? null) ? LocationDetailsDTO::fromArray(data: $data['dropoffLocation']) : null,
            passenger: ($data['passenger'] ?? null) ? PassengerDTO::fromArray(data: $data['passenger']) : null,
            driverComment: $data['driverComment'] ?? null,
            plateText: $data['plateText'] ?? null,
            services: $services,
            additionalStops: $additionalStops,
            ticketType: ($data['ticketType'] ?? null) ? TicketTypeEnum::tryFrom(value: $data['ticketType']) : null,
        );
    }
}
