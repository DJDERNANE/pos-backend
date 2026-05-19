<?php

namespace App\Http\Requests\Checkout;

use Illuminate\Foundation\Http\FormRequest;

class CheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'store_id' => ['required', 'uuid', 'exists:stores,id'],
            'payment_method' => ['required', 'string', 'max:255'],
            'discount' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}
