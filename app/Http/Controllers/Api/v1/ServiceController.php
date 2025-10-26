<?php

namespace App\Http\Controllers\Api\v1;

use App\DTO\ServiceDTO;
use App\Http\Controllers\Controller;
use App\Http\FormRequests\ServicesRequest;
use App\Services\ApiResponse;
use Illuminate\Support\Facades\Auth;

class ServiceController extends Controller
{
    public function list(ServicesRequest $request)
    {
        $user = Auth::guard('api')->user(); // если передан bearer token, то получаем текущего юзера

        $mockServices = [];
        $testPrefix = $user ? "({$user->name})" : "";

        for($i = 1; $i <= 5; $i++) {
            $mockServices[] = ServiceDTO::fromArray(data: [
                'id' => $i,
                'title' => "Услуга {$testPrefix} " . $i,
                'description' => "Описание услуги " . $i,
                'price' => rand(100, 1800),
                'currency' => 'RUB',
                'defaultState' => rand(1, 3) == 1,
                'isCountable' => rand(1, 3) == 1,
                'quantity' => 1,
            ])->toArray();
        }

        return ApiResponse::success($mockServices);
    }
}
