<?php

namespace App\Http\Controllers\Api\v1;

use App\DTO\PlaceRequestDTO;
use App\DTO\PriceRequestDTO;
use App\Exceptions\ValidationException;
use App\Http\Controllers\Controller;
use App\Http\FormRequests\SearchPlaceRequest;
use App\Http\FormRequests\SearchPriceRequest;
use App\Services\ApiResponse;
use App\Services\ClientTokenService;
use App\Services\SearchService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class SearchController extends Controller
{
    public function __construct(
        private readonly ClientTokenService $clientTokenService,
        private readonly SearchService $searchService,
    ) {}

    public function place(SearchPlaceRequest $request) {
        $user = Auth::guard('api')->user(); // если передан bearer token, то получаем текущего юзера
        $userId = $user->id ?? null;

        try {
            $clientData = $this->clientTokenService->getTokenForCurrentUser();
        } catch (\RuntimeException $e) {
            return ApiResponse::error([$e->getMessage()]);
        }

        // поиск мест
        try {
            $placeRequest = PlaceRequestDTO::fromArray(data: array_merge($request->validated(), ['token' => $clientData['token']]));
            $places = $this->searchService->fetchPlaces(placeRequestDTO: $placeRequest);
        } catch (ConnectionException $exception) {
            Log::error("User ID: {$userId}, error: {$exception->getMessage()}");

            return ApiResponse::error(['Ошибка выполнения запроса']);
        } catch (ValidationException $exception) {
            Log::error("User ID: {$userId}, error: {$exception->getMessage()}");

            return ApiResponse::error([$exception->getMessage()]);
        }

        return ApiResponse::success($places);
    }

    public function price(SearchPriceRequest $request)
    {
        $user = Auth::guard('api')->user();
        $userId = $user->id ?? null;

        try {
            $clientData = $this->clientTokenService->getTokenForCurrentUser();
        } catch (\RuntimeException $e) {
            return ApiResponse::error([$e->getMessage()]);
        }

        // поиск цен
        try {
            $priceRequest = PriceRequestDTO::fromArray(data: array_merge($request->validated(), ['token' => $clientData['token'], 'contractId' => $clientData['contractId']]));
            $prices = $this->searchService->fetchPrices(priceRequestDTO: $priceRequest);
        } catch (ConnectionException $exception) {
            Log::error("User ID: {$userId}, error: {$exception->getMessage()}");

            return ApiResponse::error(['Ошибка выполнения запроса']);
        } catch (ValidationException $exception) {
            Log::error("User ID: {$userId}, error: {$exception->getMessage()}");

            return ApiResponse::error([$exception->getMessage()]);
        }
    }
}
