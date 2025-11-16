<?php

namespace App\Services;

use App\DTO\PlaceDTO;
use App\DTO\PlaceRequestDTO;
use App\DTO\ServiceDTO;
use App\Exceptions\CustomValidationException;
use App\Models\Service;
use Cache;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

class ServiceManager
{
    /**
     * Возвращает дополнительные услуги по id полигона населенного пункта
     *
     * @throws CustomValidationException|ConnectionException
     */
    public function fetchServices(string $token, int $cityId): array {
        $req = Http::retry(times: 3, sleepMilliseconds: 100, throw: false)
            ->timeout(seconds: 60)
            ->withToken(token: $token)
            ->withUrlParameters(parameters: [
                'endpoint' => config("services.booking.endpoints.servicesEndpoint"),
                'cityId' => $cityId,
            ])->get('{+endpoint}?cityId={cityId}');

        $json = $req->json();

        if ($json['errors'] ?? false) {
            throw new CustomValidationException(message: $json['errors'][0]);
        }

        $this->saveServices(services: $json);

        return array_map(callback: function ($service) {
            return ServiceDTO::fromArray([
                "id" => $service['id'] ?? null,
                "title" => $service['title'] ?? null,
                "group" => $service['group'] ?? null,
                "price" => $service['price'] ?? null,
                "currency" => $service['currency'] ?? null,
                "defaultState" => $service['isDefaultChecked'] ?? false,
                "isCountable" => $service['isCountable'] ?? false,
                "quantity" => $service['quantity'] ?? null,
            ]);
        }, array: $json);
    }

    /**
     * Проверяет возможность дополнительного заезда по указанным координатам
     *
     * @throws CustomValidationException|ConnectionException
     */
    public function checkAdditionalStopRequest(string $token, ?string $lat, ?string $lon, ?string $pickupLat, ?string $pickupLon, ?string $dropoffLat, ?string $dropoffLon): bool {
        $cacheKey = "additional_stop.{$lat}.{$lon}.{$pickupLat}.{$pickupLon}.{$dropoffLat}.{$dropoffLon}";

        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        $req = Http::retry(times: 3, sleepMilliseconds: 100, throw: false)
            ->timeout(seconds: 60)
            ->withToken(token: $token)
            ->withUrlParameters(parameters: [
                'endpoint' => config("services.booking.endpoints.additionalStopCheckEndpoint"),
                'lat' => $lat,
                'lon' => $lon,
                'pickupLat' => $pickupLat,
                'pickupLon' => $pickupLon,
                'dropoffLat' => $dropoffLat,
                'dropoffLon' => $dropoffLon,
            ])->get('{+endpoint}?lat={lat}&lon={lon}&pickupLat={pickupLat}&pickupLon={pickupLon}&dropoffLat={dropoffLat}&dropoffLon={dropoffLon}');

        $json = $req->json();

        if ($json['errors'] ?? false) {
            throw new CustomValidationException(message: $json['errors'][0]);
        }

        Cache::put($cacheKey, $json['isAllowed'] ?? false, now()->addHours(24));

        return $json['isAllowed'] ?? false;
    }

    /**
     * Возвращает услугу по ее ID
     *
     * @param int|null $id
     * @return ServiceDTO|null
     */
    public function fetchById(?int $id): ?ServiceDTO {
        if (!$id) {
            return null;
        }

        $service = Service::query()->where("service_id", $id)->first();

        if ($service) {
            return ServiceDTO::fromArray(data: [
                "id" => $service->service_id,
                "title" => $service->title,
                "description" => $service->description,
                "price" => $service->price,
                "currency" => $service->currency,
            ]);
        }

        return null;
    }

    /**
     * Сохраняем услуги для последующего расчета стоимостей
     *
     * @param array $services
     * @return void
     */
    private function saveServices(array $services): void {
        foreach($services as $service) {
            Service::query()->updateOrCreate(attributes: [
                'service_id' => $service['id'],
            ], values: [
                'service_id' => $service['id'] ?? null,
                'title' => $service['title'] ?? null,
                'description' => $service['description'] ?? null,
                'price' => $service['price'] ?? null,
                'currency' => $service['currency'] ?? null,
                'is_countable' => $service['isCountable'] ?? false,
            ]);
        }
    }
}
