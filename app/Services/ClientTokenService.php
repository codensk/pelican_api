<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use \Psr\SimpleCache\InvalidArgumentException;

readonly class ClientTokenService
{
    public function __construct(private SearchService $searchService) {}

    // получаем токен необходимый для запросов к букинг апи
    // $setUserId - устанавливаем принудительно userId (например, для вебхуков эквайринга) когда токен не известен
    public function getTokenForCurrentUser(?int $setUserId = null): array
    {
        $user = Auth::guard('api')->user();
        $userId = $setUserId ?? ($user->id ?? null);

        try {
            return $this->searchService->fetchClientToken(userId: $userId);
        } catch (Exception | InvalidArgumentException $exception) {
            Log::error("User ID: {$userId}, error: {$exception->getMessage()}");
            throw new \RuntimeException('Ошибка выполнения запроса');
        }
    }

    public function getClientData(?int $setUserId = null): array
    {
        try {
            return $this->getTokenForCurrentUser(setUserId: $setUserId);
        } catch (\RuntimeException $e) {
            Log::error("Error fetching client token: {$e->getMessage()}");
            abort(500, 'Ошибка получения токена клиента');
        }
    }
}
