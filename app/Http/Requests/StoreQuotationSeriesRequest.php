<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreQuotationSeriesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('procurement.manage');
    }

    public function rules(): array
    {
        return [
            'supplier_id' => ['required', 'exists:suppliers,id'],
            'description' => ['nullable', 'string', 'max:255'],
            'currency' => ['nullable', 'string', 'size:3', 'alpha'],
            'purchase_type' => ['nullable', Rule::in(['local', 'import'])],
            'exchange_rate' => ['nullable', 'numeric', 'min:0'],
            'cbm_rate' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
