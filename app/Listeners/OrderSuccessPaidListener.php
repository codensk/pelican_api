<?php

namespace App\Listeners;

use App\Events\OrderCreatedEvent;
use App\Events\OrderSuccessPaidEvent;

class OrderSuccessPaidListener
{
    public function __construct() {}

    public function handle(OrderSuccessPaidEvent $event): void
    {

    }
}
