<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSaleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('sales.create') || $this->user()->can('sales.hold');
    }

    public function rules(): array
    {
        return [
            'shop_id' => ['nullable', 'integer', 'exists:shops,id'],
            'sale_id' => ['nullable', 'integer', 'exists:sales,id'],
            'customer_name' => ['nullable', 'string', 'max:120'],
            'customer_phone' => ['nullable', 'string', 'max:30'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'sale_type' => ['nullable', 'string', 'in:retail,credit'],
            'customer_account_id' => ['nullable', 'integer', 'exists:customer_accounts,id', 'required_if:sale_type,credit'],
            'vehicle_plate' => ['nullable', 'string', 'max:20', 'required_if:sale_type,credit'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id', 'distinct'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01'],
            'items.*.unit_price' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}
