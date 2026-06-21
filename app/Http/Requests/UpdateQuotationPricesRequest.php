<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateQuotationPricesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('procurement.manage');
    }

    public function rules(): array
    {
        return [
            'items' => ['required', 'array', 'min:1'],
            'items.*.id' => ['required', 'exists:quotation_items,id'],
            'items.*.unit_price' => ['nullable', 'numeric', 'min:0'],
            'items.*.unit_price_foreign' => ['nullable', 'numeric', 'min:0'],
            'items.*.transport' => ['nullable', 'numeric', 'min:0'],
            'items.*.width' => ['nullable', 'numeric', 'min:0'],
            'items.*.length' => ['nullable', 'numeric', 'min:0'],
            'items.*.height' => ['nullable', 'numeric', 'min:0'],
            'items.*.quantity_per_packet' => ['nullable', 'numeric', 'min:0.01'],
            'items.*.number_of_packets' => ['nullable', 'numeric', 'min:0'],
            'items.*.packets_override' => ['nullable', 'boolean'],
            'items.*.market_wholesale_price' => ['nullable', 'numeric', 'min:0'],
            'items.*.market_wholesale_override' => ['nullable', 'boolean'],
        ];
    }
}
