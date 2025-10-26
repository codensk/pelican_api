<?php

namespace App\Services;

use App\Exceptions\ValidationException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Log;

class ApiService
{
    public function call(callable $callback, ?int $userId)
    {
        try {
            return $callback();
        } catch (ConnectionException $exception) {
            Log::error("User ID: {$userId}, error: {$exception->getMessage()}");
            abort(500, 'Ошибка выполнения запроса');
        } catch (ValidationException $exception) {
            Log::error("User ID: {$userId}, error: {$exception->getMessage()}");
            abort(400, $exception->getMessage());
        }
    }
}
