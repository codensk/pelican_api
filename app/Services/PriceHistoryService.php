<?php

namespace App\Services;

use App\DTO\PriceResultDTO;
use App\Models\PriceHistory;

class PriceHistoryService
{
    public function savePrice(PriceResultDTO $price): void {
        PriceHistory::query()->updateOrCreate(attributes: [
            'price_id' => $price->priceId
        ], values: [
            'price_id' => $price->priceId,
            'price' => $price->toArray(),
            'expires_at' => now()->addDay(),
        ]);
    }

    public function deleteExpiredPrices(): void {
        PriceHistory::query()->where('expires_at', '<', now())->delete();
    }
}
