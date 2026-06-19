<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProcurementItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('procurement.manage');
    }

    public function rules(): array
    {
        return [
            'product_id' => ['required', 'exists:products,id'],
            'quantity' => ['required', 'numeric', 'min:0.01'],
            'unit_cost' => ['required', 'numeric', 'min:0'],
            'cbm' => ['nullable', 'numeric', 'min:0'],
            'margin' => ['nullable', 'numeric', 'min:0', 'max:500'],
        ];
    }
}
