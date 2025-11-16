<?php

namespace App\Http\Controllers\Api\v1;

use App\Events\OrderSuccessPaidEvent;
use App\Events\OrderUnsuccessfulPaidEvent;
use App\Http\Controllers\Controller;
use App\Services\BookingService;
use App\Services\PaymentService;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function __construct(
        private readonly BookingService $bookingService,
    ) {}

    public function success(Request $request)
    {
        $testOrderId = "PN-1611202523";

        event(new OrderSuccessPaidEvent(orderId: $testOrderId));

        $this->bookingService->sendOrderToBooking(orderId: $testOrderId);

        return "ok";
    }

    public function failed(Request $request)
    {
        $testOrderId = "PN-1611202523";

        event(new OrderUnsuccessfulPaidEvent(orderId: $testOrderId));

        return "ok";
    }
}
