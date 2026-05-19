<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InventoryStockResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'store_id' => $this->resource['store_id'] ?? null,
            'product_variant_id' => $this->resource['product_variant_id'] ?? null,
            'stock' => isset($this->resource['stock']) ? (float)$this->resource['stock'] : 0.0,
            'variant' => isset($this->resource['variant']) ? new VariantResource($this->resource['variant']) : null,
        ];
    }
}
