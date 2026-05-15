<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrganizationResource extends JsonResource
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

            'name' => $this->name,
            'phone' => $this->phone,
            'email' => $this->email,

            'currency_code' => $this->currency_code,

            'is_active' => $this->is_active,

            'stores_count' => $this->whenCounted('stores'),

            'created_at' => $this->created_at,
        ];
    }
}
