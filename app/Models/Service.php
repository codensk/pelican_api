<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    public $fillable = [
        'service_id', 'title', 'description',
        'price', 'currency', 'is_countable'
    ];

    protected $casts = [
        'is_countable' => 'boolean',
    ];
}
