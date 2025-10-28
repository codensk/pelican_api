<?php

namespace App\Events;

use App\DTO\UserDTO;
use Illuminate\Foundation\Events\Dispatchable;

class UserCreatedEvent
{
    use Dispatchable;

    public function __construct(
        public readonly UserDTO $user
    ) {}
}
