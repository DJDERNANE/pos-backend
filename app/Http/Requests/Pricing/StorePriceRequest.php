<?php

namespace App\Http\Requests\Pricing;

use App\Enums\UnitType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StorePriceRequest extends FormRequest
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
            'unit_type' => ['required', new Enum(UnitType::class)],
            'price' => ['required', 'numeric', 'min:0'],
            'cost_price' => ['nullable', 'numeric', 'min:0'],
            'quantity_per_unit' => ['nullable', 'integer', 'min:1'],
            'is_active' => ['boolean'],
        ];
    }
}
