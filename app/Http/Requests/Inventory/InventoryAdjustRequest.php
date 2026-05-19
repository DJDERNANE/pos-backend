<?php

namespace App\Http\Requests\Inventory;

use App\Enums\DirectionType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class InventoryAdjustRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'store_id' => ['required', 'uuid', 'exists:stores,id'],
            'product_variant_id' => ['required', 'uuid', 'exists:product_variants,id'],
            'quantity' => ['required', 'numeric', 'min:0.0001'],
            'direction' => ['required', new Enum(DirectionType::class)],
            'reference_type' => ['nullable', 'string', 'max:255'],
            'reference_id' => ['nullable', 'uuid'],
            'unit_cost' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
