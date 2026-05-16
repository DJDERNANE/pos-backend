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
            'price' => (float)$this->price,
            'cost_price' => (float)$this->cost_price,
            'quantity_per_unit' => (int)$this->quantity_per_unit,
            'is_active' => (bool)$this->is_active,
            'created_at' => $this->created_at,
        ];
    }
}
