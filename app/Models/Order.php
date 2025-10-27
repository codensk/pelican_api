<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    public $fillable = [
        'user_id', 'price_id', 'full_price',
        'order_price', 'services_price', 'order_id',
        'is_paid', 'payload'
    ];

    protected $casts = [
        'is_paid' => 'boolean',
        'payload' => 'array',
    ];
}
