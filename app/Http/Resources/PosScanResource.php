<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PosScanResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $variant = $this->resource['variant'] ?? null;
        $stock = $this->resource['stock'] ?? 0;

        return [
            'product' => new ProductResource($variant?->product),
            'product_variant' => new VariantResource($variant),
            'prices' => StorePriceResource::collection($variant?->storeVariantPrices),
            'stock' => (float)$stock,
        ];
    }
}
