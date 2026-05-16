<?php

namespace App\DTOs;

use App\Enums\UnitType;

readonly class StorePriceDTO
{
    public function __construct(
        public string $store_id,
        public string $product_variant_id,
        public UnitType $unit_type,
        public float $price,
        public float $cost_price = 0,
        public int $quantity_per_unit = 1,
        public bool $is_active = true,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            store_id: $data['store_id'],
            product_variant_id: $data['product_variant_id'],
            unit_type: UnitType::from($data['unit_type']),
            price: (float)$data['price'],
            cost_price: (float)($data['cost_price'] ?? 0),
            quantity_per_unit: (int)($data['quantity_per_unit'] ?? 1),
            is_active: $data['is_active'] ?? true,
        );
    }
}
