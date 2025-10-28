<?php

namespace App\Http\Controllers\Api\v1;

use App\DTO\UserDTO;
use App\Http\Controllers\Controller;
use App\Http\FormRequests\UserLoginRequest;
use App\Http\FormRequests\UserRegisterRequest;
use App\Services\ApiResponse;
use App\Services\UserService;
use Exception;

class LoginController extends Controller
{
    public function __construct(
        private readonly UserService $userService,
    ) {}

    public function login(UserLoginRequest $request)
    {
        try {
            $user = $this->userService->getUserByEmailAndPassword(email: $request->email, password: $request->password);
        } catch (Exception $exception) {
            abort(code: 404, message: $exception->getMessage());
        }

        return ApiResponse::success(data: [
            'token' => $user->createToken('pelican_api')->plainTextToken,
            'id' => $user->id
        ]);
    }
}
