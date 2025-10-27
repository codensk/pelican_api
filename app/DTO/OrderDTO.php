<?php

namespace App\DTO;

use App\Services\Enums\TicketTypeEnum;

class OrderDTO
{
    public function __construct(
        public ?int $userId,
        public string $priceId,
        public string $orderId,
        public array $payload,
        public bool $isPaid,
        public OrderPriceDTO $prices,
    ) {}

    public function toArray(): array
    {
        return [
            'userId' => $this->userId,
            'priceId' => $this->priceId,
            'orderId' => $this->orderId,
            'payload' => $this->payload,
            'isPaid' => $this->isPaid,
            'prices' => $this->prices->toArray(),
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            userId: $data['userId'],
            priceId: $data['priceId'],
            orderId: $data['orderId'],
            payload: $data['payload'],
            isPaid: $data['isPaid'],
            prices: OrderPriceDTO::fromArray($data['prices']),
        );
    }
}
