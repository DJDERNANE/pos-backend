<?php

namespace App\DTOs;

readonly class ProductDTO
{
    public function __construct(
        public string $organization_id,
        public string $name,
        public ?string $description = null,
        public ?string $category = null,
        public bool $is_active = true,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            organization_id: $data['organization_id'],
            name: $data['name'],
            description: $data['description'] ?? null,
            category: $data['category'] ?? null,
            is_active: $data['is_active'] ?? true,
        );
    }
}
