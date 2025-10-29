<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PriceHistory extends Model
{
    public $fillable = [
        'price_id', 'price', 'expires_at',
        'payload'
    ];

    protected $casts = [
        'price' => 'array',
        'payload' => 'array',
        'expires_at' => 'datetime',
    ];

    protected $table = 'price_history';
}
