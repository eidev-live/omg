<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreSaleTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', 'unique:sale_types,name'],
            'egg_quantity' => ['required', 'integer', 'min:1'],
            'selling_price' => ['required', 'integer', 'min:0'],
            'is_active' => ['boolean'],
        ];
    }
}
