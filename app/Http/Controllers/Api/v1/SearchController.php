<?php

namespace App\Http\Controllers\Api\v1;

use App\DTO\PlaceRequestDTO;
use App\DTO\PriceRequestDTO;
use App\Http\Controllers\Controller;
use App\Http\FormRequests\SearchPlaceRequest;
use App\Http\FormRequests\SearchPriceRequest;
use App\Services\ApiResponse;
use App\Services\ApiService;
use App\Services\ClientTokenService;
use App\Services\SearchService;
use Illuminate\Support\Facades\Auth;

class SearchController extends Controller
{
    public function __construct(
        private readonly ApiService $apiService,
        private readonly ClientTokenService $clientTokenService,
        private readonly SearchService $searchService,
    ) {}

    public function place(SearchPlaceRequest $request) {
        $clientData = $this->clientTokenService->getClientData();

        // поиск мест
        $places = $this->apiService->call(callback: fn() => $this->searchService->fetchPlaces(
            PlaceRequestDTO::fromArray(array_merge($request->validated(), ['token' => $clientData['token']]))
        ), userId: Auth::guard('api')->user()->id ?? null);

        return ApiResponse::success($places);
    }

    public function price(SearchPriceRequest $request)
    {
        $clientData = $this->clientTokenService->getClientData();

        // поиск цен
        $prices = $this->apiService->call(callback: fn() => $this->searchService->fetchPrices(
            PriceRequestDTO::fromArray(data: array_merge($request->validated(), ['token' => $clientData['token'], 'contractId' => $clientData['contractId']]))
        ), userId: Auth::guard('api')->user()->id ?? null);

        return ApiResponse::success($prices);
    }
}
