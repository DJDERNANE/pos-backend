<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InventoryResource extends JsonResource
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
            'quantity' => (float)$this->quantity,
            'low_stock_threshold' => (float)$this->low_stock_threshold,
            'is_low_stock' => $this->quantity <= $this->low_stock_threshold,
            'last_updated' => $this->updated_at,
        ];
    }
}
