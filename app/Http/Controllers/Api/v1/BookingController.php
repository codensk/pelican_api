<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Services\ApiResponse;
use App\Services\BookingService;
use App\Services\Validation\OrderValidator;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function __construct(
        private readonly OrderValidator $orderValidator,
        private readonly BookingService $bookingService,
    )
    {
    }

    public function booking(Request $request)
    {
        $data = $this->orderValidator->validate(data: $request->all());

        $orderData = $this->bookingService->processOrder(data: $data);

        return ApiResponse::success(data: ['paymentLink' => $orderData['paymentLink'], 'expiresAt' => $orderData['expiresAt']]);
    }
}
