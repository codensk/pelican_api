<?php

namespace App\Listeners;

use App\Events\OrderCreatedEvent;

class OrderCreatedListener
{
    public function __construct() {}

    public function handle(OrderCreatedEvent $event): void
    {

    }
}
