<?php

namespace App\Events;

use App\DTO\OrderDTO;
use App\DTO\UserDTO;
use Illuminate\Foundation\Events\Dispatchable;

class OrderBookingFailedEvent
{
    use Dispatchable;

    public function __construct(
        public readonly OrderDTO $orderDTO,
        public string $reason,
    ) {}
}
