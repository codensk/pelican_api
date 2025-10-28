<?php

namespace App\Models;

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
}
