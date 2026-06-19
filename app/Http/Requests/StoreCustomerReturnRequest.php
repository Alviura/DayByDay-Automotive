<?php

namespace App\Http\Requests;

use App\Models\Sale;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCustomerReturnRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('returns.create');
    }

    public function rules(): array
    {
        return [
            'sale_id' => ['required', 'integer', 'exists:sales,id'],
            'reason' => ['required', 'string', 'max:255'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01'],
            'items.*.unit_price' => ['nullable', 'numeric', 'min:0'],
            'items.*.condition' => ['required', Rule::in(['good', 'damaged'])],
            'items.*.restock' => ['nullable', 'boolean'],
            'items.*.replacement' => ['nullable', 'boolean'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $sale = Sale::with('items')->find($this->sale_id);

            if (! $sale || $sale->status !== 'completed') {
                $validator->errors()->add('sale_id', 'Returns can only be created against completed sales.');

                return;
            }

            foreach ($this->items ?? [] as $index => $line) {
                $saleItem = $sale->items->firstWhere('product_id', $line['product_id'] ?? null);

                if (! $saleItem) {
                    $validator->errors()->add("items.{$index}.product_id", 'Product was not part of this sale.');

                    continue;
                }

                if ((float) $line['quantity'] > (float) $saleItem->quantity) {
                    $validator->errors()->add("items.{$index}.quantity", 'Return quantity exceeds sold quantity.');
                }
            }
        });
    }

    public function sale(): Sale
    {
        return Sale::with('shop')->findOrFail($this->sale_id);
    }
}
