<?php

namespace App\DTOs;

readonly class CreateOrganizationDTO
{
    public function __construct(
        public string $name,
        public string $email,
        public ?string $phone = null,
        public ?string $currency_code = null,
        public ?string $timezone = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            email: $data['email'],
            phone: $data['phone'] ?? null,
            currency_code: $data['currency_code'] ?? null,
            timezone: $data['timezone'] ?? null,
        );
    }
}
