<?php

namespace App\Events;

use App\DTO\OrderDTO;
use App\DTO\UserDTO;
use Illuminate\Foundation\Events\Dispatchable;

class OrderCreatedEvent
{
    use Dispatchable;

    public function __construct(
        public readonly OrderDTO $orderDTO,
    ) {}
}
