<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSaleTypeRequest extends FormRequest
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
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('sale_types', 'name')->ignore($this->route('sale_type')),
            ],
            'egg_quantity' => ['required', 'integer', 'min:1'],
            'selling_price' => ['required', 'integer', 'min:0'],
            'is_active' => ['boolean'],
        ];
    }
}
