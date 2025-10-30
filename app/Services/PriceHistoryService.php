<?php

namespace App\Services;

use App\DTO\PriceResultDTO;
use App\Models\PriceHistory;

class PriceHistoryService
{
    public function savePrice(PriceResultDTO $price, array $payload): void {
        $price = $price->toArray();

        PriceHistory::query()->updateOrCreate(attributes: [
            'price_id' => $price['priceId']
        ], values: [
            'price_id' => $price['priceId'],
            'price' => $price,
            'payload' => $payload,
            'expires_at' => now()->addDay(),
        ]);
    }

    public function deleteExpiredPrices(): void {
        PriceHistory::query()->where('expires_at', '<', now())->delete();
    }

    public function fetchById(string $priceId): ?PriceHistory {
        return PriceHistory::query()->where('price_id', $priceId)->first();
    }
}
