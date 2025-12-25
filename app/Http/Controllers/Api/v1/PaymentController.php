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
        $testOrderId = $request->id ?? "PN-1711202524";

        event(new OrderSuccessPaidEvent(orderId: $testOrderId));

        $this->bookingService->sendOrderToBooking(orderId: $testOrderId);

        return "ok";
    }

    public function failed(Request $request)
    {
        $testOrderId = $request->id ?? "PN-1711202524";

        event(new OrderUnsuccessfulPaidEvent(orderId: $testOrderId));

        return "ok";
    }
}
