<?php

namespace App\DTO;

use App\Services\Enums\UserRoleEnum;

class UserDTO
{
    public function __construct(
        public ?int $id = null,
        public string $name,
        public string $email,
        public ?string $password,
        public UserRoleEnum $role,
    ) {}

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'password' => $this->password,
            'role' => $this->role,
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? null,
            name: $data['name'] ?? null,
            email: $data['email'] ?? null,
            password: $data['password'] ?? null,
            role: is_string(value: $data['role']) ? UserRoleEnum::tryFrom(value: $data['role']) : $data['role'],
        );
    }
}
