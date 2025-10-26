<?php

namespace App\Http\Controllers\Api\v1;

use App\DTO\PriceRequestDTO;
use App\Exceptions\ValidationException;
use App\Http\Controllers\Controller;
use App\Http\FormRequests\SearchPriceRequest;
use App\Services\ApiResponse;
use App\Services\SearchService;
use Exception;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Psr\SimpleCache\InvalidArgumentException;

class SearchController extends Controller
{
    public function __construct(
        private readonly SearchService $searchService
    ) {}

    public function price(SearchPriceRequest $request)
    {
        $user = Auth::guard('api')->user(); // если передан bearer token, то получаем текущего юзера
        $userId = $user->id ?? null;

        // получаем токен необходимый для запросов к букинг апи
        try {
            $clientData = $this->searchService->fetchClientToken(userId: $userId);
        } catch (Exception | InvalidArgumentException $exception) {
            Log::error("User ID: {$userId}, error: {$exception->getMessage()}");

            return ApiResponse::error(['Ошибка выполнения запроса']);
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
