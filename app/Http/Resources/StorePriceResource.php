<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StorePriceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'store_id' => $this->store_id,
            'product_variant_id' => $this->product_variant_id,
            'variant' => new VariantResource($this->whenLoaded('variant')),
            'unit_type' => $this->unit_type,
            'sell_price' => (float)$this->sell_price,
            'buy_price' => $this->buy_price === null ? null : (float)$this->buy_price,
            'price' => (float)$this->sell_price,
            'cost_price' => $this->buy_price === null ? null : (float)$this->buy_price,
            'quantity_per_unit' => (int)$this->quantity_per_unit,
            'is_default' => (bool)$this->is_default,
            'created_at' => $this->created_at,
        ];
    }
}
