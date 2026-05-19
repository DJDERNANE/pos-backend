<?php

declare(strict_types=1);

namespace App\DTOs;

readonly class CheckoutDTO
{
    public function __construct(
        public string $store_id,
        public string $payment_method,
        public float $discount = 0.0,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            store_id: $data['store_id'],
            payment_method: $data['payment_method'],
            discount: isset($data['discount']) ? (float)$data['discount'] : 0.0,
        );
    }
}
