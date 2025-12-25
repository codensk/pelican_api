<?php

namespace App\DTO;

use App\Models\Service;
use App\Services\Enums\TicketTypeEnum;

class BookingRequestDTO
{
    public function __construct(
        public string $priceId,
        public ?LocationDetailsDTO $pickupLocation,
        public ?LocationDetailsDTO $dropoffLocation,

        public ?PassengerDTO $passenger,
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

        foreach($data['services'] ?? [] as $serviceItem) {
            $service = Service::query()->where("id", $serviceItem["id"])->first();
            $serviceItem['serviceCode'] = $service->service_code ?? null;
            $serviceItem['title'] = $service->title ?? null;
            $serviceItem['description'] = $service->description ?? null;
            $serviceItem['price'] = $service->price ?? null;
            $serviceItem['group'] = $service->group ?? null;
            $serviceItem['currency'] = $service->currency ?? null;
            $serviceItem['isCountable'] = $service->is_countable ?? false;

            $services[] = ServiceDTO::fromArray(data: $serviceItem);
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
