<?php

namespace App\Services;

use App\DTO\PlaceDTO;
use App\DTO\PlaceRequestDTO;
use App\DTO\ServiceDTO;
use App\Exceptions\CustomValidationException;
use App\Models\Service;
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
