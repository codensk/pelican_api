<?php

namespace App\Models;

use App\DTO\OrderDTO;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    public $fillable = [
        'user_id', 'notification_email', 'price_id', 'full_price',
        'order_price', 'services_price', 'order_id', 'payment_link',
        'is_paid', 'payload', 'full_price_refundable', 'is_refundable',
        'refundable_ticket_percent', 'expires_at', 'price_payload',
        'vehicle_class_id'
    ];

    protected $casts = [
        'is_refundable' => 'boolean',
        'is_paid' => 'boolean',
        'payload' => 'array',
        'price_payload' => 'array',
        'expires_at' => 'timestamp',
    ];

    public function user(): HasOne {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    public function markAsPaid(): void {
        $this->update(['is_paid' => true]);
    }

    public function toDto(): OrderDTO {
        return OrderDTO::fromArray(data: [
            'userId' => $this->user_id,
            'notificationEmail' => $this->notification_email,
            'vehicleClassId' => $this->vehicle_class_id,
            'priceId' => $this->price_id,
            'orderId' => $this->order_id,
            'payload' => $this->payload,
            'pricePayload' => $this->price_payload ?? [],
            'isPaid' => $this->is_paid,
            'paymentLink' => $this->payment_link,
            'expiresAt' => Carbon::parse($this->expires_at),
            'refundableTicketPercent' => $this->refundable_ticket_percent,
            'isRefundable' => $this->is_refundable,
            'createdAt' => Carbon::parse($this->created_at),
            'prices' => [
                'fullPrice' => $this->full_price,
                'fullPriceRefundable' => $this->full_price_refundable,
                'tripPrice' => $this->order_price,
                'servicePrice' => $this->services_price,
            ],
        ]);
    }
}
