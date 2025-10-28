<?php

namespace App\DTO;

use App\Services\Enums\TicketTypeEnum;
use Carbon\Carbon;

class OrderDTO
{
    public function __construct(
        public ?int $userId,
        public string $priceId,
        public string $orderId,
        public float $refundableTicketPercent,
        public array $payload,
        public Carbon $expiresAt,
        public bool $isPaid,
        public bool $isRefundable,
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
            'expiresAt' => $this->expiresAt,
            'refundableTicketPercent' => $this->refundableTicketPercent,
            'isRefundable' => $this->isRefundable,
            'prices' => $this->prices->toArray(),
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            userId: $data['userId'],
            priceId: $data['priceId'],
            orderId: $data['orderId'],
            refundableTicketPercent: $data['refundableTicketPercent'],
            payload: $data['payload'],
            expiresAt: ($data['expiresAt'] ?? null) ? Carbon::parse($data['expiresAt']) : null,
            isPaid: $data['isPaid'],
            isRefundable: $data['isRefundable'],
            prices: OrderPriceDTO::fromArray($data['prices']),
        );
    }
}
