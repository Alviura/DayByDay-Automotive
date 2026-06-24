<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BulkQuotationItemsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('procurement.manage');
    }

    public function rules(): array
    {
        return [
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.order_quantity' => ['required', 'numeric', 'min:0.01'],
            'items.*.quantity' => ['sometimes', 'numeric', 'min:0.01'],
        ];
    }
}
