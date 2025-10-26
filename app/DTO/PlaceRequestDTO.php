<?php

namespace App\DTO;

use App\Services\Enums\UserRoleEnum;
use Illuminate\Support\Carbon;

class PlaceRequestDTO
{
    public function __construct(
        public string $token,
        public string $search,
    ) {}

    public function toArray(): array
    {
        return [
            'token' => $this->token,
            'search' => $this->search,
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            token: $data['token'],
            search: $data['search'],
        );
    }
}
