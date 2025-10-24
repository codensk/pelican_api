<?php

namespace App\Listeners;

use App\Events\UserCreatedEvent;
use Illuminate\Support\Facades\Log;

class UserCreatedListener
{
    public function __construct()
    {
    }

    public function handle(UserCreatedEvent $event): void
    {
        Log::error("test");
    }
}
