<?php

namespace App\Http\Requests\Variant;

use Illuminate\Foundation\Http\FormRequest;

class VariantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'product_id' => ['required', 'uuid', 'exists:products,id'],
            'name' => ['required', 'string', 'max:255'],
            'sku' => ['nullable', 'string', 'max:100'],
            'is_default' => ['boolean'],
            'is_active' => ['boolean'],
        ];
    }
}
