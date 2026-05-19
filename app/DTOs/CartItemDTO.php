<?php

declare(strict_types=1);

namespace App\DTOs;

use App\Enums\UnitType;

readonly class CartItemDTO
{
    public function __construct(
        public string $store_id,
        public ?string $product_variant_id,
        public ?string $store_variant_price_id,
        public UnitType $unit_type,
        public float $quantity,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            store_id: $data['store_id'],
            product_variant_id: $data['product_variant_id'] ?? null,
            store_variant_price_id: $data['store_variant_price_id'] ?? null,
            unit_type: UnitType::from($data['unit_type']),
            quantity: (float)$data['quantity'],
        );
    }
}
