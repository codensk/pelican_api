<?php

namespace App\Models;

use App\DTO\OrderDTO;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    public $fillable = [
        'user_id', 'price_id', 'full_price',
        'order_price', 'services_price', 'order_id',
        'is_paid', 'payload', 'full_price_refundable', 'is_refundable',
        'refundable_ticket_percent', 'expires_at'
    ];

    protected $casts = [
        'is_refundable' => 'boolean',
        'is_paid' => 'boolean',
        'payload' => 'array',
        'expires_at' => 'timestamp',
    ];

    public function toDto(): OrderDTO {
        return OrderDTO::fromArray(data: [
            'userId' => $this->user_id,
            'priceId' => $this->price_id,
            'orderId' => $this->order_id,
            'payload' => $this->payload,
            'isPaid' => $this->is_paid,
            'expiresAt' => Carbon::parse($this->expires_at),
            'refundableTicketPercent' => $this->refundable_ticket_percent,
            'isRefundable' => $this->is_refundable,
            'prices' => [
                'fullPrice' => $this->full_price,
                'fullPriceRefundable' => $this->full_price_refundable,
                'tripPrice' => $this->order_price,
                'servicePrice' => $this->services_price,
            ],
        ]);
    }
}
