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
        private readonly PaymentService $paymentService,
        private readonly BookingService $bookingService,
    ) {}

    public function success(Request $request)
    {
        $testOrderId = "PN-U0tOUL3hyW";

        event(new OrderSuccessPaidEvent(orderId: $testOrderId));

        $this->bookingService->sendOrderToBooking(orderId: $testOrderId);
    }

    public function failed(Request $request)
    {
        $testOrderId = "PN-HIS49M5V9I";

        event(new OrderUnsuccessfulPaidEvent(orderId: $testOrderId));
    }
}
