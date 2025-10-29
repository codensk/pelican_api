<?php

namespace App\Services;

use App\DTO\PlaceDTO;
use App\DTO\PlaceRequestDTO;
use App\DTO\PriceRequestDTO;
use App\DTO\PriceResultDTO;
use App\Exceptions\CustomValidationException;
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
     * Получаем токен и contract id необходимые для запросов к букинг апи
     *
     * @throws ConnectionException
     * @throws InvalidArgumentException
     * @throws Exception
     */
    public function fetchClientToken(?int $userId): ?array {
        $cacheKey = "fetchClientToken.{$userId}";

        if (Cache::has($cacheKey)) {
          //  return Cache::get($cacheKey);
        }

        $endpoint = config("services.booking.endpoints.clientTokenEndpoint");

        $req = Http::retry(times: 3, sleepMilliseconds: 100)
            ->timeout(seconds: 60)
            ->get("{$endpoint}?userId={$userId}");

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
        $refundableTicketPercent = $json['refundableTicketPercent'] ?? 0;
        $employeeId = $json['employeeId'] ?? null;

        $returnData = $clientToken && $contractId ? [
            'token' => $clientToken,
            'contractId' => $contractId,
            'refundableTicketPercent' => (float) $refundableTicketPercent,
            'employeeId' => $employeeId,
        ] : null;

        if (($json['token'] ?? null) && ($json['contractId'] ?? null)) {
            Cache::set(key: $cacheKey, value: $returnData, ttl: now()->addDays(30));
        }

        return $returnData;
    }

    /**
     * Ищет место по названию или адресу, возвращает список мест с адресами и координатами
     *
     * @throws CustomValidationException|ConnectionException
     */
    public function fetchPlaces(PlaceRequestDTO $placeRequestDTO): array {
        $req = Http::retry(times: 3, sleepMilliseconds: 100, throw: false)
            ->timeout(seconds: 60)
            ->withToken(token: $placeRequestDTO->token)
            ->withUrlParameters(parameters: [
                'endpoint' => config("services.booking.endpoints.placeEndpoint"),
                'term' => $placeRequestDTO->search,
            ])->get('{+endpoint}?term={term}');

        $json = $req->json();

        if ($json['errors'] ?? false) {
            throw new CustomValidationException(message: $json['errors'][0]);
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
     * @throws CustomValidationException
     */
    public function fetchPrices(PriceRequestDTO $priceRequestDTO, ?float $refundableTicketPercent = 0): array {
        $pickupAddress = $priceRequestDTO->pickupLocation->address;
        $dropoffAddress = $priceRequestDTO->dropoffLocation->address;

        $req = Http::retry(times: 3, sleepMilliseconds: 100, throw: false)
            ->timeout(seconds: 60)
            ->withToken(token: $priceRequestDTO->token)
            ->withUrlParameters(parameters: [
               'endpoint' => config("services.booking.endpoints.priceEndpoint"),
               'tripTypeId' => 1, // 1 - трансфер, 2 - аренда
               'contractId' => $priceRequestDTO->contractId, // договор
               'pickupAt' => $priceRequestDTO->pickupAt->format("Y-m-d H:i"), // дата/время подачи
               'pickupLocation' => $priceRequestDTO->pickupLocation->name . " ({$pickupAddress})", // адрес подачи
               'pickupLat' => $priceRequestDTO->pickupLocation->lat,
               'pickupLon' => $priceRequestDTO->pickupLocation->lon,
               'dropoffLocation' => $priceRequestDTO->dropoffLocation->name . " ({$dropoffAddress})", // адрес назначения
               'dropoffLat' => $priceRequestDTO->dropoffLocation->lat,
               'dropoffLon' => $priceRequestDTO->dropoffLocation->lon,
               'tollRoad' => true, // использовать платные дороги
            ])->get('{+endpoint}?tripTypeId={tripTypeId}&contractId={contractId}&pickupAt={pickupAt}&pickup[place][lat]={pickupLat}&pickup[place][lon]={pickupLon}&dropoff[place][lat]={dropoffLat}&dropoff[place][lon]={dropoffLon}&tollRoad={tollRoad}');
//            ])->get('{+endpoint}?tripTypeId={tripTypeId}&contractId={contractId}&pickupAt={pickupAt}&pickup[place][address]={pickupLocation}&pickup[place][lat]={pickupLat}&pickup[place][lon]={pickupLon}&dropoff[place][address]={dropoffLocation}&dropoff[place][lat]={dropoffLat}&dropoff[place][lon]={dropoffLon}&tollRoad={tollRoad}');

        $json = $req->json();

        if ($json['errors'] ?? false) {
            throw new CustomValidationException(message: $json['errors'][0]);
        }

        $results = array_map(callback: function ($price) use ($refundableTicketPercent) {
            $ticketPrice = ($price['prices']['fullPrice'] ?? null) ? (double) $price['prices']['fullPrice'] : null;
            $priceRefundableTicket = $this->calcRefundableTicketPrice(ticketPrice: $ticketPrice ?? 0, refundableTicketPercent: $refundableTicketPercent);

            return PriceResultDTO::fromArray([
                "priceId" => $price['entryId'],
                "vehicleClassId" => $price['carClassId'],
                "maxPassengers" => $price['maxPassengers'],
                "distance" => $price['tripDistance'] ?? null,
                "duration" => $price['tripMinutes'] ?? null,
                "price" => $ticketPrice,
                "priceRefundableTicket" => number_format($priceRefundableTicket, 2, ".", ""),
                "refundableTicketPercent" => $refundableTicketPercent,
                "currency" => ($price['prices']['fullPrice'] ?? null) ? ($price['prices']['currency'] ?? null) : null,
                "pickupPlaceTypeId" => $price['pickup']['polygons']['polygonTypeId'] ?? null,
                "dropoffPlaceTypeId" => $price['dropoff']['polygons']['polygonTypeId'] ?? null,
            ]);
        }, array: $json);

        // Исключаем цены под запрос (в пеликане не нужны)
        return $this->excludePricesByRequest(prices: $results);
    }

    public function calcRefundableTicketPrice(float $ticketPrice, float $refundableTicketPercent = 0): float {
        return $ticketPrice * (1 + $refundableTicketPercent / 100);
    }

    private function excludePricesByRequest(array $prices): array {
        return array_values(array: array_filter(array: $prices, callback: function ($price) {
            return $price->price > 0;
        }));
    }
}
