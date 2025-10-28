<?php

namespace App\Http\Controllers\Api\v1;

use App\DTO\UserDTO;
use App\Http\Controllers\Controller;
use App\Http\FormRequests\UserLoginRequest;
use App\Http\FormRequests\UserRegisterRequest;
use App\Services\ApiResponse;
use App\Services\UserService;
use Exception;

class RegistrationController extends Controller
{
    public function __construct(
        private readonly UserService $userService,
    ) {}

    public function register(UserRegisterRequest $request)
    {
        $user = $this->userService->create(userDTO: UserDTO::fromArray(data: $request->validated()));

        return ApiResponse::success(data: $user->makeApiModel(), code: 201);
    }
}
