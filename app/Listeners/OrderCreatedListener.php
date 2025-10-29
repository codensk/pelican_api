<?php

namespace App\Listeners;

use App\Events\OrderCreatedEvent;
use App\Jobs\SendPaymentLinkOnOrderCreatedJob;

class OrderCreatedListener
{
    public function __construct() {}

    public function handle(OrderCreatedEvent $event): void
    {
    }
}
