<?php

namespace App\DTO;

use App\Services\Enums\TicketTypeEnum;
use Carbon\Carbon;

class OrderDTO
{
    public function __construct(
        public ?int $userId,
        public ?string $notificationEmail,
        public string $priceId,
        public ?int $vehicleClassId,
        public string $orderId,
        public float $refundableTicketPercent,
        public array $payload,
        public array $pricePayload,
        public ?string $paymentLink,
        public Carbon $expiresAt,
        public bool $isPaid,
        public bool $isRefundable,
        public OrderPriceDTO $prices,
        public ?Carbon $createdAt = null,
    ) {}

    public function toArray(): array
    {
        return [
            'userId' => $this->userId,
            'notificationEmail' => $this->notificationEmail,
            'priceId' => $this->priceId,
            'vehicleClassId' => $this->vehicleClassId,
            'orderId' => $this->orderId,
            'payload' => $this->payload,
            'pricePayload' => $this->pricePayload,
            'isPaid' => $this->isPaid,
            'paymentLink' => $this->paymentLink,
            'expiresAt' => $this->expiresAt,
            'refundableTicketPercent' => $this->refundableTicketPercent,
            'isRefundable' => $this->isRefundable,
            'prices' => $this->prices->toArray(),
            'createdAt' => $this->createdAt,
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            userId: $data['userId'],
            notificationEmail: $data['notificationEmail'],
            priceId: $data['priceId'],
            vehicleClassId: $data['vehicleClassId'],
            orderId: $data['orderId'],
            refundableTicketPercent: $data['refundableTicketPercent'],
            payload: $data['payload'],
            pricePayload: $data['pricePayload'],
            paymentLink: $data['paymentLink'] ?? null,
            expiresAt: ($data['expiresAt'] ?? null) ? Carbon::parse($data['expiresAt']) : null,
            isPaid: $data['isPaid'],
            isRefundable: $data['isRefundable'],
            prices: OrderPriceDTO::fromArray($data['prices']),
            createdAt: ($data['prices'] ?? null) ? Carbon::parse($data['createdAt']) : null,
        );
    }
}
