<?php

namespace App\Http\Controllers\Api\v1;

use App\DTO\UserDTO;
use App\Http\Controllers\Controller;
use App\Http\FormRequests\UserLoginRequest;
use App\Http\FormRequests\UserRegisterRequest;
use App\Services\ApiResponse;
use App\Services\UserService;
use Auth;
use Exception;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    public function __construct(
        private readonly UserService $userService,
    )
    {
    }

    public function login(UserLoginRequest $request)
    {
        if (Auth::attempt($request->validated(), remember: true)) {
            $request->session()->regenerate();

            return ApiResponse::success();
        }

//        try {
//            $this->userService->getUserByEmailAndPassword(email: $request->email, password: $request->password);
//        } catch (Exception $exception) {
//            abort(code: 404, message: $exception->getMessage());
//        }

        throw new HttpResponseException(response()->json([
            'message' => __('Неверный email или пароль'),
        ], 404));
    }

    public function logout(Request $request)
    {
        $user =  Auth::guard('api')->user();

        if ($user) {
            Auth::guard('web')->logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        return ApiResponse::success();
    }
}
