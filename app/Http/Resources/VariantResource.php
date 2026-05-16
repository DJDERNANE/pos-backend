<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VariantResource extends JsonResource
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
            'product_id' => $this->product_id,
            'name' => $this->name,
            'sku' => $this->sku,
            'is_default' => (bool)$this->is_default,
            'is_active' => (bool)$this->is_active,
            'barcodes' => BarcodeResource::collection($this->whenLoaded('barcodes')),
            'prices' => StorePriceResource::collection($this->whenLoaded('storeVariantPrices')),
            'created_at' => $this->created_at,
        ];
    }
}
