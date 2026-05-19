<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'cart_id' => $this->cart_id,
            'product_variant_id' => $this->product_variant_id,
            'store_variant_price_id' => $this->store_variant_price_id,
            'unit_type' => $this->unit_type,
            'quantity' => (float)$this->quantity,
            'unit_price' => (float)$this->unit_price,
            'total_price' => (float)$this->total_price,
            'product_variant' => new VariantResource($this->whenLoaded('productVariant')),
            'store_variant_price' => new StorePriceResource($this->whenLoaded('storeVariantPrice')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
