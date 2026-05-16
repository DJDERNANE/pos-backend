<?php

namespace App\Http\Requests\Barcode;

use Illuminate\Foundation\Http\FormRequest;

class BarcodeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'barcode' => ['required', 'string', 'max:50', 'unique:product_barcodes,barcode'],
            'type' => ['nullable', 'string', 'max:20'],
        ];
    }
}
