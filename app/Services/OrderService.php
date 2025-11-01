<?php

namespace App\Services;

use App\Models\Order;

class OrderService
{
    public function fetchByCode(string $code): ?Order {
        return Order::query()->where('code', $code)->first();
    }
}
