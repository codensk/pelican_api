<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PriceHistory extends Model
{
    public $fillable = [
        'price_id', 'price', 'expires_at'
    ];

    protected $casts = [
        'price' => 'array',
        'expires_at' => 'datetime',
    ];

    protected $table = 'price_history';
}
