<?php

namespace App\Http\Requests\Cart;

use App\Enums\UnitType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class AddToCartRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'store_id' => ['required', 'uuid', 'exists:stores,id'],
            'product_variant_id' => ['nullable', 'uuid', 'exists:product_variants,id'],
            'store_variant_price_id' => ['nullable', 'uuid', 'exists:store_variant_prices,id'],
            'unit_type' => ['required', new Enum(UnitType::class)],
            'quantity' => ['required', 'numeric', 'min:0.0001'],
        ];
    }
}
