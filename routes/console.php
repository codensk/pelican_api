<?php

// удаляем истекшие токены
Schedule::command('sanctum:prune-expired --hours=24')->name(description: "Clean expired tokens")->everyFiveMinutes()->withoutOverlapping();

// удаляем истекшие прайсы
Schedule::call(function (\App\Services\PriceHistoryService $priceHistoryService) {
    $priceHistoryService->deleteExpiredPrices();
})->name(description: "Clean expired prices")->everySixHours()->withoutOverlapping();
