<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $items = CartItemResource::collection($this->whenLoaded('cartItems'));

        return [
            'id' => $this->id,
            'store_id' => $this->store_id,
            'user_id' => $this->user_id,
            'status' => $this->status,
            'items' => $items,
            'item_count' => $items->count(),
            'subtotal' => (float)$this->cartItems->sum(fn ($item) => $item->total_price),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
