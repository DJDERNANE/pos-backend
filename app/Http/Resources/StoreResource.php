<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StoreResource extends JsonResource
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

            'organization_id' => $this->organization_id,

            'name' => $this->name,

            'phone' => $this->phone,
            'address' => $this->address,
            'city' => $this->city,

            'is_active' => $this->is_active,

            'created_at' => $this->created_at,
        ];
    }
}
