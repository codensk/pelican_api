<?php

namespace App\Services;

use App\DTO\PlaceDTO;
use App\DTO\PlaceRequestDTO;
use App\DTO\PriceRequestDTO;
use App\Exceptions\ValidationException;
use Exception;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Psr\SimpleCache\InvalidArgumentException;

/**
 *
 */
class SearchService
{
    /**
     * Эндпоинт запроса цен
     *
     * @var string
     */
    private string $priceEndpoint = 'https://sandbox.busfer.com/api/v1/prices';
    private string $placeEndpoint = 'https://sandbox.busfer.com/api/v1/findPlace';
    private string $clientTokenEndpoint = 'https://release.busfer.com/api/pelican/clientToken';

    /**
     * Получаем токен и contract id необходимые для запросов к букинг апи
     *
     * @throws ConnectionException
     * @throws InvalidArgumentException
     * @throws Exception
     */
    public function fetchClientToken(?int $userId): ?array {
        $cacheKey = "fetchClientToken1.{$userId}";

        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        $req = Http::retry(times: 3, sleepMilliseconds: 100)
            ->timeout(seconds: 60)
            ->get("{$this->clientTokenEndpoint}?userId={$userId}");

        $token = null;

        if ($req->successful()) {
            $token = $req->json('token');
        }

        if (!$token && $userId) {
            // запрос от зарегистрированного пользователя, но токена почему-то нет
            throw new Exception(message: "Токен не доступен");
        }

        $json = $req->json();

        // если нет токена, то возвращаем дефолтный токен и договор (физ лица бронируют от имени отдельного клиента в букинге)
        $clientToken = ($json['token'] ?? null) ?? config("services.booking.defaultClientToken");
        $contractId = ($json['contractId'] ?? null) ?? config("services.booking.defaultClientContractId");

        $returnData = $clientToken && $contractId ? [
            'token' => $clientToken,
            'contractId' => $contractId,
        ] : null;

        if (($json['token'] ?? null) && ($json['contractId'] ?? null)) {
            Cache::set(key: $cacheKey, value: $returnData, ttl: now()->addDays(30));
        }

        return $returnData;
    }

    /**
     * Ищет место по названию или адресу, возвращает список мест с адресами и координатами
     *
     * @throws ValidationException|ConnectionException
     */
    public function fetchPlaces(PlaceRequestDTO $placeRequestDTO): array {
        $req = Http::retry(times: 3, sleepMilliseconds: 100, throw: false)
            ->timeout(seconds: 60)
            ->withToken(token: $placeRequestDTO->token)
            ->withUrlParameters(parameters: [
                'endpoint' => $this->placeEndpoint,
                'term' => $placeRequestDTO->search,
            ])->get('{+endpoint}?term={term}');

        $json = $req->json();

        if ($json['errors'] ?? false) {
            throw new ValidationException(message: $json['errors'][0]);
        }

        return array_map(callback: function ($place) {
            return PlaceDTO::fromArray([
                "name" => $place['name'] ?? null,
                "address" => $place['address'] ?? null,
                "lat" => ($place['lat'] ?? null) ? (double) $place['lat'] : null,
                "lon" => ($place['lon'] ?? null) ? (double) $place['lon'] : null,
            ]);
        }, array: $json);
    }

    /**
     * Возвращает цены по указанному запросу
     *
     * @throws ConnectionException
     * @throws ValidationException
     */
    public function fetchPrices(PriceRequestDTO $priceRequestDTO): array {
        $req = Http::retry(times: 3, sleepMilliseconds: 100, throw: false)
            ->timeout(seconds: 60)
            ->withToken(token: $priceRequestDTO->token)
            ->withUrlParameters(parameters: [
               'endpoint' => $this->priceEndpoint,
               'tripTypeId' => 1, // 1 - трансфер, 2 - аренда
               'contractId' => $priceRequestDTO->contractId, // договор
               'pickupAt' => $priceRequestDTO->pickupAt->format("Y-m-d H:i"), // дата/время подачи
               'pickupLocation' => $priceRequestDTO->pickupLocation, // адрес подачи
               'pickupLat' => $priceRequestDTO->pickupLat,
               'pickupLon' => $priceRequestDTO->pickupLon,
               'dropoffLocation' => $priceRequestDTO->dropoffLocation, // адрес назначения
               'tollRoad' => true, // использовать платные дороги
            ])->get('{+endpoint}?tripTypeId={tripTypeId}&contractId={contractId}&pickupAt={pickupAt}&pickup[place][address]={pickupLocation}&pickup[place][lat]={pickupLat}&pickup[place][lon]={pickupLon}&dropoff[place][address]={dropoffLocation}&dropoff[place][lat]={dropoffLat}&dropoff[place][lon]={dropoffLon}&tollRoad={tollRoad}');

        $json = $req->json();

        if ($json['errors'] ?? false) {
            throw new ValidationException(message: $json['errors'][0]);
        }
dd($json);
        return [];
    }
}
