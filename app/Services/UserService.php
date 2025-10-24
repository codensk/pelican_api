<?php

namespace App\Services;

use App\DTO\UserDTO;
use App\Events\UserCreatedEvent;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class UserService
{
    public function create(UserDTO $userDTO): User {
        if (!$userDTO->password) {
            $userDTO->password = Str::random(10);
        }

        $userData = [
            'name' => $userDTO->name,
            'email' => $userDTO->email,
            'password' => $userDTO->password,
        ];

        $user = User::query()->create($userData);

        $user->setRole(role: $userDTO->role);

        $user->refresh();

        event(new UserCreatedEvent(user: UserDTO::fromArray(data: $userData)));

        return $user;
    }

    /**
     * @throws Exception
     */
    public function sendPasswordOnRegister(UserDTO $userDTO): void {
        $appName = config('app.name');

        try {
            MailService::sendPasswordOnRegister(to: $userDTO->email, subject: "Регистрация на сайте {$appName}", email: $userDTO->email, password: $userDTO->password);
        } catch (Exception $exception) {
            Log::error(message: $exception->getMessage());

            throw new Exception(message: "Ошибка отправки письма на {$userDTO->email}");
        }
    }
}
