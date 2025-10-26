<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\FormRequests\BookingRequest;
use App\Services\BookingService;

class BookingController extends Controller
{
    public function __construct(
        private readonly BookingService $bookingService
    ) {}

    public function booking(BookingRequest $request)
    {

    }
}
