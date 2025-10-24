<?php

namespace App\Services;

use App\DTO\UserDTO;
use App\Events\UserCreatedEvent;
use App\Models\User;

class UserService
{
    public function create(UserDTO $userDTO): User {
        $user = User::query()->create([
            'name' => $userDTO->name,
            'email' => $userDTO->email,
            'password' => $userDTO->password,
        ]);

        $user->setRole(role: $userDTO->role);

        $user->refresh();

        event(new UserCreatedEvent(user: $user->makeDTO()));

        return $user;
    }
}
