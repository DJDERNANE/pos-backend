<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateOrganizationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:organizations,email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'currency_code' => ['nullable', 'string', 'size:3'],
            'timezone' => ['nullable', 'timezone'],
        ];
    }
}
