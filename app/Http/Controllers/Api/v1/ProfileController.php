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
}
