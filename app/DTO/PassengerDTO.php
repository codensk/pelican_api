<?php

namespace App\DTO;

use Carbon\Carbon;

class PassengerDTO
{
    public function __construct(
        public ?string $firstName,
        public ?string $lastName,
        public ?string $phone,
        public ?string $secondaryPhone,
        public ?string $email,
        public int $numberOfPassengers = 1
    ) {}

    public function toArray(): array
    {
        return [
            'firstName' => $this->firstName,
            'lastName' => $this->lastName,
            'phone' => $this->phone,
            'secondaryPhone' => $this->secondaryPhone,
            'email' => $this->email,
            'numberOfPassengers' => $this->numberOfPassengers,
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            firstName: $data['firstName'],
            lastName: $data['lastName'],
            phone: $data['phone'],
            secondaryPhone: $data['secondaryPhone'],
            email: $data['email'],
            numberOfPassengers: $data['numberOfPassengers'],
        );
    }
}
