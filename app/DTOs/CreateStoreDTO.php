<?php

namespace App\DTOs;

readonly class CreateStoreDTO
{
    public function __construct(
        public string $organization_id,
        public string $name,
        public ?string $type = null,
        public ?string $phone = null,
        public ?string $address = null,
        public ?string $city = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            organization_id: $data['organization_id'],
            name: $data['name'],
            type: $data['type'] ?? null,
            phone: $data['phone'] ?? null,
            address: $data['address'] ?? null,
            city: $data['city'] ?? null,
        );
    }
}
