<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\FormRequests\AdditionalStopRequest;
use App\Http\FormRequests\ServicesRequest;
use App\Services\ApiResponse;
use App\Services\ApiService;
use App\Services\ClientTokenService;
use App\Services\ServiceManager;
use Illuminate\Support\Facades\Auth;

class ServiceController extends Controller
{
    public function __construct(
        private readonly ApiService $apiService,
        private readonly ClientTokenService $clientTokenService,
        private readonly ServiceManager $serviceManager,
    ) {}

    public function list(ServicesRequest $request)
    {
        $clientData = $this->clientTokenService->getClientData();

        // поиск мест
        $services = $this->apiService->call(callback: fn() => $this->serviceManager->fetchServices(token: $clientData['token'], lat: $request->lat, lon: $request->lon, lang: $request->lang), userId: Auth::guard('api')->user()->id ?? null);

        return ApiResponse::success($services);
    }

    public function checkAdditionalStopAvailability(AdditionalStopRequest $request)
    {
        $clientData = $this->clientTokenService->getClientData();

        $isAllowed = $this->apiService->call(callback: fn() => $this->serviceManager->checkAdditionalStopRequest(
            token: $clientData['token'],
            lat: $request->lat,
            lon: $request->lon,
            pickupLat: $request->pickupLat,
            pickupLon: $request->pickupLon,
            dropoffLat: $request->dropoffLat,
            dropoffLon: $request->dropoffLon
        ), userId: Auth::guard('api')->user()->id ?? null);

        return ApiResponse::success([
            'isAllowed' => $isAllowed
        ]);
    }
}
