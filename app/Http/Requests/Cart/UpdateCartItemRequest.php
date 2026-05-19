<?php

namespace App\Http\Requests\Cart;

use App\Enums\UnitType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class UpdateCartItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'quantity' => ['required', 'numeric', 'min:0'],
            'unit_type' => ['nullable', new Enum(UnitType::class)],
            'store_variant_price_id' => ['nullable', 'uuid', 'exists:store_variant_prices,id'],
        ];
    }
}
