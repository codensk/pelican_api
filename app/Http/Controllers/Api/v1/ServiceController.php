<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
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
        $services = $this->apiService->call(callback: fn() => $this->serviceManager->fetchServices(token: $clientData['token'], cityId: $request->cityId), userId: Auth::guard('api')->user()->id ?? null);

        return ApiResponse::success($services);
    }
}
