<?php

namespace App\Events;

use App\DTO\OrderDTO;
use App\DTO\UserDTO;
use Illuminate\Foundation\Events\Dispatchable;

class OrderUnsuccessfulPaidEvent
{
    use Dispatchable;

    public function __construct(
        public readonly string $orderId,
    ) {}
}
