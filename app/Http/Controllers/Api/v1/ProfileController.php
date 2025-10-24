<?php

namespace App\Http\Controllers\Api\v1;

use App\DTO\UserDTO;
use App\Http\Controllers\Controller;
use App\Http\FormRequests\UserLoginRequest;
use App\Http\FormRequests\UserRegisterRequest;
use App\Http\FormRequests\UserUpdateRequest;
use App\Services\ApiResponse;
use App\Services\UserService;
use Exception;
use Illuminate\Support\Facades\Log;

class ProfileController extends Controller
{
    public function __construct(
        private readonly UserService $userService,
    ) {}

    public function profile(UserUpdateRequest $request)
    {
        try {
            $this->userService->update(id: $request->user()->id, data: $request->validated());
        } catch (Exception $exception) {
            return ApiResponse::error([$exception->getMessage()], code: 500);
        }

        return ApiResponse::success();
    }

    // booking methods
    public function directProfile(UserUpdateRequest $request)
    {
        $userId = $request->userId ?? null;

        if (!$userId || $request->ip() != config("services.booking.ip")) {
            abort(404);
        }

        try {
            $user = $this->userService->getUser(id: $userId);
        } catch (Exception $exception) {
            return ApiResponse::error([$exception->getMessage()], code: 500);
        }

        return ApiResponse::success($user->makeApiModel());
    }

    public function directUpdate(UserUpdateRequest $request)
    {
        $userId = $request->userId ?? null;

        if (!$userId || $request->ip() != config("services.booking.ip")) {
            abort(404);
        }

        try {
            $this->userService->update(id: $userId, data: $request->validated());
        } catch (Exception $exception) {
            return ApiResponse::error([$exception->getMessage()], code: 500);
        }

        return ApiResponse::success();
    }
}
