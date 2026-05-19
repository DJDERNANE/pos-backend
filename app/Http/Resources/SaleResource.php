<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SaleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'organization_id' => $this->organization_id,
            'store_id' => $this->store_id,
            'user_id' => $this->user_id,
            'cart_id' => $this->cart_id,
            'invoice_number' => $this->invoice_number,
            'subtotal' => (float)$this->subtotal,
            'discount_total' => (float)$this->discount_total,
            'tax_total' => (float)$this->tax_total,
            'total' => (float)$this->total,
            'payment_method' => $this->payment_method,
            'status' => $this->status,
            'sale_items' => SaleItemResource::collection($this->whenLoaded('saleItems')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
