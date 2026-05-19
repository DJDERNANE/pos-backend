<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InventoryMovementResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'organization_id' => $this->organization_id,
            'store_id' => $this->store_id,
            'product_variant_id' => $this->product_variant_id,
            'type' => $this->type,
            'quantity' => (float)$this->quantity,
            'direction' => $this->direction,
            'reference_type' => $this->reference_type,
            'reference_id' => $this->reference_id,
            'unit_cost' => $this->unit_cost === null ? null : (float)$this->unit_cost,
            'created_by' => $this->created_by,
            'notes' => $this->notes,
            'created_at' => $this->created_at,
            'product_variant' => new VariantResource($this->whenLoaded('productVariant')),
        ];
    }
}
