<?php

namespace App\Http\Requests\Store;

use Illuminate\Foundation\Http\FormRequest;

class AssignUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => ['required', 'uuid', 'exists:users,id'],
            'role' => ['required', 'string', 'in:cashier,manager,admin'],
        ];
    }
}
