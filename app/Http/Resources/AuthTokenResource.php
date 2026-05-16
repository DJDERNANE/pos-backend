<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AuthTokenResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'token' => $this['token'],
            'user' => new UserResource($this['user']),
            'organization_role' => $this['organization_role'],
            'stores' => StoreResource::collection($this['stores']),
            'store_count' => $this['store_count'],
            'redirect' => $this['redirect'] ?? null,
        ];
    }
}
