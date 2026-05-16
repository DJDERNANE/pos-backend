<?php

namespace App\DTOs;

readonly class BarcodeDTO
{
    public function __construct(
        public string $product_variant_id,
        public string $barcode,
        public string $type = 'GTIN',
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            product_variant_id: $data['product_variant_id'],
            barcode: $data['barcode'],
            type: $data['type'] ?? 'GTIN',
        );
    }
}
