<?php

namespace App\DTOs;

readonly class VariantDTO
{
    public function __construct(
        public string $product_id,
        public string $name,
        public ?string $sku = null,
        public bool $is_default = false,
        public bool $is_active = true,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            product_id: $data['product_id'],
            name: $data['name'],
            sku: $data['sku'] ?? null,
            is_default: $data['is_default'] ?? false,
            is_active: $data['is_active'] ?? true,
        );
    }
}
