<?php

namespace App\Http\Requests\Cart;

use App\Enums\UnitType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class ScanCartRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'store_id' => ['required', 'uuid', 'exists:stores,id'],
            'barcode' => ['required', 'string', 'max:255'],
            'unit_type' => ['nullable', new Enum(UnitType::class)],
            'quantity' => ['nullable', 'numeric', 'min:0.0001'],
        ];
    }
}
