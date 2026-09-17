<?php

namespace App\Http\Requests;

use App\Enums\DeliveryStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSaleRequest extends FormRequest
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
            'sale_date' => ['required', 'date'],
            'customer_id' => ['required', 'integer', 'exists:customers,id'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.sale_type_id' => ['required', 'integer', 'exists:sale_types,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'paid_amount' => ['nullable', 'integer', 'min:0'],
            'payment_date' => ['nullable', 'date'],
            'delivery_status' => ['required', Rule::enum(DeliveryStatus::class)],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'sale_date' => 'tanggal',
            'customer_id' => 'customer',
            'items' => 'item',
            'items.*.sale_type_id' => 'tipe penjualan',
            'items.*.quantity' => 'jumlah',
            'paid_amount' => 'jumlah dibayar',
            'payment_date' => 'tanggal pembayaran',
            'delivery_status' => 'status pengiriman',
            'notes' => 'catatan',
        ];
    }
}
