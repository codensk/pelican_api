<?php

namespace App\Http\Controllers;

use App\Services\OrderService;

class OrderController extends Controller
{
    public function __construct(
        private readonly OrderService $orderService
    ) {}

    public function show(string $orderId)
    {
        $order = $this->orderService->fetchByCode(code: $orderId);

        if (!$order) {
            abort(404);
        }

        $order = $order->toDto();

        return view('OrderController.show', compact('order'));
    }
}
