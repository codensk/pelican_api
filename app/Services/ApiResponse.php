<?php

namespace App\Services;

class ApiResponse
{
    public static function success(array $data = [], int $code = 200): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $data
        ], $code);
    }

    public static function error(array $errors = [], int $code = 500): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'success' => false,
            'errors' => $errors
        ], $code);
    }
}
